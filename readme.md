Laravel-Translatable-DB
====================


![Laravel Translatable DB](img/laravel-translatable.png)

**If you want to store translations of your models into the database, this package is for you.**

This is a Laravel package for translatable models. Its goal is to remove the complexity in retrieving and storing multilingual model instances. With this package you write less code, as the translations are being fetched/saved when you fetch/save your instance.

### Docs

* [Demo](#demo)
* [Laravel compatibility](#laravel-compatibility)
* [Tutorial](#tutorial)
* [Installation](#installation-in-4-steps)
* [Configuration](#configuration)
* [Features list](#features-list)
* [Support](#faq)

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
 5.4      | 1.*
 5.3      | 1.*
 5.2      | 1.*
 5.1      | 1.*



## Installation in 4 steps

### Step 1: Install package

Add the package in your composer.json by executing the command.

```bash
composer require flobbos/laravel-translatable-db
```

Next, add the service provider to `app/config/app.php`

```
Flobbos\TranslatableDB\TranslatableDBServiceProvider::class,
```

### Step 2: Migrations

In this example, we want to translate the model `Country`. We will need an extra table `country_translations` amd 
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

### Step 3: Models

1. The translatable model `Country` should [use the trait](http://www.sitepoint.com/using-traits-in-php-5-4/) `Flobbos\TranslatableDB\TranslatableDB`. 
2. The convention for the translation model is `CountryTranslation`.


```php
// models/Country.php
class Country extends Eloquent {
    
    use \Flobbos\TranslatableDB\TranslatableDB;
    
    public $translatedAttributes = ['name'];
    protected $fillable = ['code'];
    
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

### Step 4: Configuration

Laravel 4.*
```bash
php artisan config:publish flobbos/laravel-translatable-db
```

Laravel 5.*
```bash
php artisan vendor:publish 
```

With this command, initialize the configuration and modify the created file, located under `app/config/packages/flobbos/laravel-translatable-db/translatable.php`.

*Note: There isn't any restriction for the format of the locales. Feel free to use whatever suits you better, like "eng" instead of "en", or "el" instead of "gr".  The important is to define your locales and stick to them.*

## Configuration

### The config file

You can see the options for further customization in the [config file](src/config/translatable.php).

### The translation model

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
    use Translatable;

    public $translationModel = 'MyApp\Models\CountryAwesomeTranslation';
}

```

## Features list

