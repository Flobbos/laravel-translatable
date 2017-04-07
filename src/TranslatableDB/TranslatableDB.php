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
     * Main translation function to spit out translation based on locale
     * @param bool $withFallback return untranslated attribute if no translation is found
     * @return string
     */
    public function translate($withFallback = true){
        return $this->getTranslation($withFallback);
    }
    
    public function getTranslation($withFallback = true){
        $locale = $this->getLocale();
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
        
        if ($this->isTranslationAttribute($attribute)) {
            if ($this->getTranslation($locale) === null && !in_array($key, $this->getFallbackAttributes())) {
                return null;
            }
            elseif($this->getTranslation($locale) === null && in_array($key, $this->getFallbackAttributes())){
                
                return parent::getAttribute($key);
            }
            // If the given $attribute has a mutator, we push it to $attributes and then call getAttributeValue
            // on it. This way, we can use Eloquent's checking for Mutation, type casting, and
            // Date fields.
            if ($this->hasGetMutator($attribute)) {
                $this->attributes[$attribute] = $this->getTranslation($locale)->$attribute;
                return $this->getAttributeValue($attribute);
            }
            return $this->getTranslation($locale)->$attribute;
        }
        return parent::getAttribute($key);
    }
    
    private function getTranslationByLocaleKey($key){
        foreach ($this->translations as $translation) {
            if ($translation->getAttribute($this->getLocaleKey()) == $key) {
                return $translation;
            }
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
        return [$key, $this->locale()];
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
     * Either get a string value from Laravel locale or a value from a lang array in config
     * @return string language key
     */
    public function getLocale(){
        //Database configuration
        if($this->getConfigKey('use_db')){
            $class = $this->languageModel ?: $this->getConfigKey('language_model');
            $model = new $class;
            $lang = $model->where('locale',$this->locale())->first();
            if(!is_null($lang)){
                return $lang->id;
            }
            return $this->getConfigKey('use_fallback') ? $this->getConfigKey('fallback_locale_id') :null;
        }
        //Config based configuration
        else{
            $lngArr = $this->getConfigKey('language_array');
            if(!isset($lngArr[$this->locale()][$this->getLocaleKey()])){
                return $this->getConfigKey('fallback_locale');
            }
            return $lngArr[$this->locale()][$this->getLocaleKey()];
        }
        return $this->locale();
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
        return $config->get('translatable.'.$key);
    }

}