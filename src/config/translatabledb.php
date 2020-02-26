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
    | Eloquent model to be used for languages
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
    'use_fallback' => true,
    
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
    | Native mode
    |--------------------------------------------------------------------------
    |
    | If native mode is set to true it is assumed that the fallback translation
    | resides in the same table as the model that needs to be translated. If set
    | to false the fallback translation should be located in the translation
    | table.
    |
    */
    'native_mode' => false,

    /*
    |--------------------------------------------------------------------------
    | Locale key
    |--------------------------------------------------------------------------
    |
    | Defines the 'locale' field name, which is used by the
    | translation model. This will also define the value pushed into the request
    | by the middleware.
    |
    */
    'locale_key' => 'language_id',
    
    /*
    |--------------------------------------------------------------------------
    | Locale column name
    |--------------------------------------------------------------------------
    |
    | This defines the locale column in your language table. By default it is
    | assumed that it's named 'locale' (for 'de','en' etc.) if this doesn't 
    | suit your database structure you can define a different column name here.
    |
    */
    'locale_column' => 'locale',

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
    
    /*
    |--------------------------------------------------------------------------
    | Load translation ID into every route
    |--------------------------------------------------------------------------
    | By setting this to false the middleware that provides the language ID
    | parameter will only be loaded into the routes you specify by manually
    | inserting the middleware as desired. If set to true the middleware will
    | be loaded upon every request. 
    |
     */
    'middleware_default' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Handle polymorphic translations
    |--------------------------------------------------------------------------
    | By setting this to true TranslatableDB will assume that your translations
    | are set up with a polymorphic table and act accordingly.
    |
     */
    'polymorphic' => false,
];
