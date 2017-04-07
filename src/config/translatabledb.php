<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database driver
    |--------------------------------------------------------------------------
    |
    | If you prefer using the database to store your languages for 
    | added flexibility set this to true and also set the proper
    | values in the following config options
    |
    */
    'use_db' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Use Database
    |--------------------------------------------------------------------------
    |
    | If you prefer using the database to store your languages for 
    | added flexibility set this to true and also set the proper
    | values in the following config options
    |
    */
    'language_model' => 'App\Language',
    
    /*
    |--------------------------------------------------------------------------
    | Language Array
    |--------------------------------------------------------------------------
    |
    | If you are not using the database for your languages you can
    | setup a language array here. The default is provided as a guideline.
    | The language_id parameter is the identifier for the translation model
    |
    */
    'language_array'        => [
	    'de' => ['name' => 'Deutsch', 'language_id' => '1'],
	    'en' => ['name' => 'English', 'language_id' => '2'],
	    'fr' => ['name' => 'Français', 'language_id' => '3']
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Use fallback
    |--------------------------------------------------------------------------
    |
    | Determine if fallback locales are returned by default or not. If 
    | set to true, translatable will try and get a the default translation
    | defined in the following option. If set to false, translatable assumes
    | that your default values are stored in the same table as the main model
    | and will try to get the values from there as defined in the
    | $fallbackAttributes array option. If not found, null will be returned.
    |
    */
    'use_fallback' => false,
    
    /*
    |--------------------------------------------------------------------------
    | Fallback Locale (config based)
    |--------------------------------------------------------------------------
    |
    | A fallback locale is the locale being used to return a translation
    | when the requested translation is not existing. To disable it
    | set it to false.
    |
    */
    'fallback_locale' => 'de',
    
    /*
    |--------------------------------------------------------------------------
    | Fallback Locale ID (database)
    |--------------------------------------------------------------------------
    |
    | Just like the fallback_locale above this does the exact same thing
    | but only applies if driver is set to database and returns the default
    | language ID.
    |
    */
    'fallback_locale_id' => 1,

    /*
    |--------------------------------------------------------------------------
    | Locale key
    |--------------------------------------------------------------------------
    |
    | Defines the 'locale' field name, which is used by the
    | translation model.
    |
    */
    'locale_key' => 'language_id',

    /*
    |--------------------------------------------------------------------------
    | Always load translations when converting to array
    |--------------------------------------------------------------------------
    | Setting this to false will have a performance improvement but will
    | not return the translations when using toArray(), unless the
    | translations relationship is already loaded.
    |
     */
    'to_array_always_loads_translations' => true,
];
