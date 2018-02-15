<?php 

namespace Flobbos\TranslatableDB;

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait TranslatableDB {
    
    
    public function __isset($key){
        return $this->isTranslationAttribute($key) || parent::__isset($key);
    }
    
    /**
     * Define translation relationship
     * @return type
     */
    public function translations(){
        return $this->hasMany($this->getTranslationModelName(),$this->getRelationKey());
    }
    
    /**
     * Return the default name for the translation maodel
     * @return string
     */
    public function getTranslationModelNameDefault(){
        return get_class($this).'Translation';
    }
    
    /**
     * Retrieve the model name of the translation model
     * @return type
     */
    public function getTranslationModelName(){
        return $this->translationModel ?: $this->getTranslationModelNameDefault();
    }
    
    /**
     * Retrieve the currently set languageModel or from config
     * @return string
     */
    public function getLanguageModelName(){
        return isset($this->languageModel) ? $this->languageModel : $this->getConfigKey('language_model');
    }
    
    /**
     * Main translation function to spit out translation based on locale
     * @param bool $withFallback return untranslated attribute if no translation is found
     * @return string
     */
    public function translate($locale = null){
        return $this->getTranslation($locale);
    }
    
    public function getTranslation($locale = null){
        $locale = $locale ?:$this->getLocale();
        if ($translation = $this->getTranslationByLocaleKey($locale)) {
            return $translation;
        }
        return null;
    }
    
    /**
     * Magic property for getting the translated value without calling translate()
     * @param type $key
     * @return type
     */
    public function getAttribute($key){
        list($attribute, $locale) = $this->getAttributeAndLocale($key);

        $translation = null;
        //If Fallback is used
        if ($this->isTranslationAttribute($attribute) && $this->useFallback()) {
            if (is_null($this->getTranslation($locale)) && !in_array($key, $this->getFallbackAttributes())) {
                return null;
            }
            elseif(is_null($this->getTranslation($locale)) && in_array($key, $this->getFallbackAttributes())){
                if($this->nativeMode()){
                    return parent::getAttribute($key);
                }
                elseif(!is_null($this->getTranslation($this->getFallbackKey()))){
                    $translation = $this->getTranslation($this->getFallbackKey())->$attribute;
                }
                else{
                    return null;
                }
            }
            else{
                $translation = $this->getTranslation($locale)->$attribute;
            }
            // If the given $attribute has a mutator, we push it to $attributes and then call getAttributeValue
            // on it. This way, we can use Eloquent's checking for Mutation, type casting, and
            // Date fields.
            if ($this->hasGetMutator($attribute)) {
                $this->attributes[$attribute] = $translation;
                return $this->getAttributeValue($attribute);
            }
            return $translation;
        }
        //No fallback in use
        elseif($this->isTranslationAttribute($attribute)){
            //If there's no translation
            if ($this->getTranslation($locale) === null) {
                return null;
            }
            //Check for Mutator
            if ($this->hasGetMutator($attribute)) {
                $this->attributes[$attribute] = $this->getTranslation($locale)->$attribute;
                return $this->getAttributeValue($attribute);
            }
            //Return translation
            return $this->getTranslation($locale)->$attribute;
        }
        return parent::getAttribute($key);
    }
    
    private function getTranslationByLocaleKey($key, $try_fallback = true){
        foreach ($this->translations as $translation) {
            if ($translation->getAttribute($this->getLocaleKey()) == $key) {
                return $translation;
            }
        }
        //We're still here so nothing was sent
        //try the fallback if needed and not tried yet
        if($this->useFallback() && $try_fallback){
            return $this->getTranslationByLocaleKey($this->getFallbackKey(), false);
        }
        return null;
    }
    /**
     * Magic array function for getting parsed attributes with translations
     * @return array
     */
    public function toArray(){
        $attributes = parent::toArray();

        if ($this->relationLoaded('translations') || $this->getConfigKey('to_array_always_loads_translations')) {
            // continue
        } else {
            return $attributes;
        }

        $hiddenAttributes = $this->getHidden();

        foreach ($this->translatedAttributes as $field) {
            if (in_array($field, $hiddenAttributes)) {
                continue;
            }

            if ($translations = $this->getTranslation()) {
                $attributes[$field] = $translations->$field;
            }
        }

        return $attributes;
    }
    
    /**
     * Get attribute and matching locale
     * @param mixed $key
     * @return array
     */
    private function getAttributeAndLocale($key){
        if (str_contains($key, ':')) {
            return explode(':', $key);
        }
        return [$key, $this->getLocale()];
    }
    
    public function getRelationKey(){
        if ($this->translationForeignKey) {
            $key = $this->translationForeignKey;
        } elseif ($this->primaryKey !== 'id') {
            $key = $this->primaryKey;
        } else {
            $key = $this->getForeignKey();
        }
        return $key;
    }
    
    /**
     * Get locale key to look for in translation table
     * @return string 
     */
    public function getLocaleKey(){
        return $this->localeKey ?: $this->getConfigKey('locale_key');
    }
    
    /**
     * Check if current attribute is translatable
     * @param mixed $key
     * @return bool true/false
     */
    protected function isTranslationAttribute($key){
        return in_array($key, $this->translatedAttributes);
    }
    
    /**
     * Check if current attribute is a fallback attribute
     * @param mixed $key
     * @return bool true/false
     */
    protected function isFallbackAttribute($key){
        return in_array($key, $this->fallbackAttributes);
    }
    
    /**
     * Get fallback attributes if they are set
     * @return array
     */
    protected function getFallbackAttributes(){
        return $this->fallbackAttributes ?: [];
    }
    
    /**
     * Check if fallback should be used.
     * @return bool
     */
    protected function useFallback(){
        return $this->getConfigKey('use_fallback');
    }
    
    /**
     * Detect if native mode fallback is to be used
     * @return bool
     */
    protected function nativeMode(){
        return $this->getConfigKey('native_mode');
    }
    
    /**
     * Get the fallback key 
     * @return mixed string locale or int ID
     */
    protected function getFallbackKey(){
        if(!$this->getConfigKey('use_db')){
            return $this->getConfigKey('fallback_locale');
        }
        return $this->getConfigKey('fallback_locale_id');
    }
    
    /**
     * Either get a string value from Laravel locale or a value from a lang array in config
     * @return string language key
     */
    public function getLocale(){
        return request()->get('language_id')?:$this->locale();
    }
    
    /**
     * Get current system locale
     * @return string locale 
     */
    protected function locale(){
        return app()->make('translator')->getLocale();
    }
    
    /**
     * Get config parameter
     * @param string $key
     * @return mixed value
     */
    protected function getConfigKey($key){
        $config = app()->make('config');
        return $config->get('translatabledb.'.$key);
    }

}
