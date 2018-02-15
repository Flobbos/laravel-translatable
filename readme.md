Laravel-Translatable-DB
====================


![Laravel Translatable DB](img/laravel-translatable.png)

**If you want to store translations of your models into the database, this package is for you.**

This is a Laravel package for translatable models. Its goal is to remove the complexity in retrieving and storing multilingual model instances. With this package you write less code, as the translations are being fetched/saved when you fetch/save your instance.

### Docs

* [Demo](#demo)
* [Laravel compatibility](#laravel-compatibility)
* [Installation](#installation)
* [Configuration](#configuration)

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

Next, add the service provider to `config/app.php`

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

### Step 4: Configuration

Laravel ^5.3.*
```bash
php artisan vendor:publish 
```

With this command, initialize the configuration and modify the created file, located under `app/config/packages/flobbos/laravel-translatable-db/translatable.php`.


## Configuration

### The config file

You can see the options for further customization in the [config file](src/config/translatabledb.php).

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
    use TranslatableDB;

    public $translationModel = 'MyApp\Models\CountryAwesomeTranslation';
}

```

