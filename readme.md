Laravel-Translatable-DB
====================


![Laravel Translatable DB](img/laravel-translatable.png)

**Adding multilingual support to your models with ease**

The package is based on Based on https://github.com/dimsav/laravel-translatable
but with the added twist of loading the translation based on a `language_id` rather
than a string based `locale` from the translations tables.

The package simply deals with retrieving translated content. If you want an 
easy way of storing your content you should check out https://github.com/Flobbos/laravel-crudable
which deals with saving content in your database in a compatible way. 


### Docs

* [Demo](#demo)
* [Laravel compatibility](#laravel-compatibility)
* [Installation](#installation)
* [Configuration](#configuration)
* [Translation Model](#translation-model)
* [Middleware](#middleware)

## Demo

**Getting translated attributes**

```php
  $greece = Country::where('code', 'gr')->first();
  echo $greece->translate('en')->name; // Greece
  
  App::setLocale('en');
  echo $greece->name;     // Greece

  App::setLocale('de');
  echo $greece->name;     // Griechenland
```


## Laravel compatibility

 Laravel  | Translatable
:---------|:----------
 5.5      | 1.4.*
 5.4      | 1.4.*
 5.3      | 1.4.*



## Installation

### Install package

Add the package in your composer.json by executing the command.

```bash
composer require flobbos/laravel-translatable-db
```

Next, add the service provider to `config/app.php`. This step is not necessary
if auto discover didn't work. 

```
Flobbos\TranslatableDB\TranslatableDBServiceProvider::class,
```

### Migrations method 1

In this example, we want to translate the model `Country`. We will need an extra table `country_translations` and 
an extra table `languages`.

```php

Schema::create('languages', function(Blueprint $table)
{
    $table->increments('id');
    $table->string('locale');
    $table->string('name');
}

Schema::create('countries', function(Blueprint $table)
{
    $table->increments('id');
    $table->string('code');
    $table->timestamps();
});

Schema::create('country_translations', function(Blueprint $table)
{
    $table->increments('id');
    $table->integer('country_id')->unsigned();
    $table->string('name');
    $table->string('language_id')->index();

    $table->unique(['country_id','language_id']);
    $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
    $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
});
```

### Migrations method 2

```php

Schema::create('languages', function(Blueprint $table)
{
    $table->increments('id');
    $table->string('locale');
    $table->string('name');
}

Schema::create('countries', function(Blueprint $table)
{
    $table->increments('id');
    $table->string('code');
    $table->string('name'); //the original name lives in this table
    $table->timestamps();
});

Schema::create('country_translations', function(Blueprint $table)
{
    $table->increments('id');
    $table->integer('country_id')->unsigned();
    $table->string('name');
    $table->string('language_id')->index();

    $table->unique(['country_id','language_id']);
    $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
    $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
});
```

Method 2 assumes that your default translation lives in the same table as 
the model that is to be translated. In this case you have to set:

```php
public $fallbackAttributes = ['name'];
```

The default translation will then be pulled from the original model's table.

### Step 3: Models

1. The translatable model `Country` should [use the trait](http://www.sitepoint.com/using-traits-in-php-5-4/) `Flobbos\TranslatableDB\TranslatableDB`. 
2. The convention for the translation model is `CountryTranslation`.


```php
// models/Country.php
class Country extends Eloquent {
    
    use \Flobbos\TranslatableDB\TranslatableDB;
    
    public $translatedAttributes = ['name'];
    public $fallbackAttributes = ['name']; 
    protected $fillable = ['code'];
    //protected $fillable = ['code','name']; //if method 2 was used
    
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    // (optionaly)
    // protected $with = ['translations'];

}

// models/CountryTranslation.php
class CountryTranslation extends Eloquent {

    public $timestamps = false;
    protected $fillable = ['name'];

}
```

The array `$translatedAttributes` contains the names of the fields being translated in the "Translation" model.

### Step 4: Publish config

Laravel ^5.3.*
```bash
php artisan vendor:publish 
```

With this command, initialize the configuration and modify the created file, located under `app/config/translatable.php`.


## Configuration

### DB Usage

If you want to load languages from a table in the datbase you need to set this
to true in the config.

```php
    'use_db' => true,
```

### Language model

The language model is set to `App\Language` by default but it can be anything
you want.

```php
    'language_model' => 'App\Whatever'
```

### Language array

If you prefer to use the config file for storing your language based information
you can setup the language array to your needs. We will still be relying on
the language_id as the identifier for each language. 

```php 
    'language_array'        => [
        'de' => ['name' => 'Deutsch', 'language_id' => '1'],
        'en' => ['name' => 'English', 'language_id' => '2'],
        'fr' => ['name' => 'Français', 'language_id' => '3']
    ]
```

### Fallback

Sometimes translations can be missing. In this case we can use a fallback 
translation to prevent missing content on a page. 

```php
    'use_fallback' => true,
```

If you don't want to use a fallback, simply set this to false and you will get
`null` if there's no suitable translation available.

### Default fallback

The default fallback varies depending on DB or non-DB usage in the package. You
can set these options in the config with either:

```php
    'fallback_locale' => 'de'
```

or

```php
    'fallback_locale_id' => 1,
```

### Native mode

Sometimes content has already been added to a table that later needs to be translated.
To prevent the mess of migrating existing content into our translation tables 
we have the option to use native mode. This will assume the default content lives
in the table of the translated model. 

```php
    'native_mode' => true
```

### Locale key

The key used for finding the corresponding translation in our translations table.
We assume language_id as this is used throughout the package but it can be whatever
you set it to be here.

```php
    'locale_key' => 'language_id',
```

This will also set the value pushed into the request by the middleware. 

### Locale column

We need to identify the current locale by calling `app()->getLocale()` and find
the corresponding language in the database. In case your language identifier is 
called something other than 'locale' in the DB, you can set it here:

```php
    'locale_column' => 'locale',
```

### toArray translations

It gets tricky when your translated models are output in an array or JSON format.
If the translation isn't loaded into the model it will get omitted when either
function is called. With this setting you can force the translation into the model. 
Beware of unnecessary n-queries!

```php
    'to_array_always_loads_translations' => true,
```

### middleware_default

If you don't want to use the middleware on all routes, then set this to false
and add the middleware where needed.

```php
    'middleware_default' => false
```

## Translation Model

The convention used to define the class of the translation model is to append the keyword `Translation`.

So if your model is `\MyApp\Models\Country`, the default translation would be `\MyApp\Models\CountryTranslation`.

To use a custom class as translation model, define the translation class (including the namespace) as parameter. For example:

```php
<?php 

namespace MyApp\Models;

use Flobbos\TranslatableDB\TranslatableDB;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Country extends Eloquent
{
    use TranslatableDB;

    public $translationModel = 'MyApp\Models\CountryAwesomeTranslation';
}

```

## Middleware

### Default

By default the middleware is pushed into the kernel by the service provider. 
If DB use is set to true in the config, the middleware resolves the language
model set in the config and loads the 'language_id' parameter into every request.
With this language_id property the corresponding translation is automatically
loaded into the model. 

### Custom

If you only want specific routes to load translated content from the DB you can
set this in the config:

```php
    'middleware_default' => false
```

Then you need to manually register the middleware for the routes where needed.

