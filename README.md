# Laravel Scout Elasticsearch Driver

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package makes is the [Elasticsearch](https://www.elastic.co/products/elasticsearch) driver for Laravel Scout.

## Contents

- [Installation](#installation)
- [Usage](#usage)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

``` bash
composer require tamayo/laravel-scout-elastic
```

You must add the Scout service provider and the package service provider in your app.php config:

```php
// config/app.php
'providers' => [
    ...
    Laravel\Scout\ScoutServiceProvider::class,
    ...
    ScoutEngines\Elasticsearch\ElasticsearchProvider::class,
],
```

Then you should publish the Elasticsearch configuration using the `vendor:publish` Artisan command.

```
php artisan vendor:publish --provider="ScoutEngines\Elasticsearch\ElasticsearchProvider"
```

### Setting up Elasticsearch configuration
You must have a Elasticsearch server up and running with the index you want to use created

If you need help with this please refer to the [Elasticsearch documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/index.html)

After you've published the Laravel Scout package configuration:

```php
// config/scout.php
// Set your driver to elasticsearch
    'driver' => env('SCOUT_DRIVER', 'elasticsearch'),
```

## Usage

### Creating Elasticsearch indexes with proper mapping

You may define custom mappings for Elasticserch fields in the config. The config has examples.
If you prefer storing mappings in models, you may create a static public method `mapping()` in each particular model :

```
class Property extends Model
{
    // ...
    public static function mapping() {
        return [
            'location' => [
                'type' => 'geo_point'
            ],
        ];
    }
    // ...
}
```
And then use it from the config:
```
 'indices' => [

    'realestate' => [
        'settings' => [
            "number_of_shards" => 1,
            "number_of_replicas" => 0,
        ],
        'mappings' => [
            'properties' => \App\Property::mapping(),
        ],
    ],
 ]

```
Elasticsearch can set default types to model fields on the first insert if you do not explicitly define them. 
However, sometimes the defaults are not what you're looking for.

In that case, we strongly recommend creating indexes with proper mappings before inserting any data.
For that purpose, there is an Artisan's command, called `elastic:make-index {index?}` which creates indexes based on
your config.

To create all indexes from your config just ignore the {index?} parameter and run:

```
php artisan elastic:make-index
```

Please note: this command will remove all existing indexes with the same names.

To get information about your existing Elasticsearch indexes you may want to use the following command:

```
php artisan elastic:indices
```

### Indexing data

You may follow instructions from the [official Laravel Scout documentation](https://laravel.com/docs/5.3/scout)
to index your data.

### Search

The package supports everything that is provided by Laravel Scout.
However, to use the extra Elasticsearch features included in this package, use trait `ElasticSearchable` 
by adding it to your model instead of `Searchable`:

```
class Article extends Model
{
    // use Searchable;
    use ElasticSearchable;
    // ...
}
```

The package features:
 
1) The `elasticSearch` method:

```
$articles = Article::elasticSearch('multi_match', $q, [
    'fields' => ['title', 'content', 'tags'],
    'fuzziness' => 'auto',
    'prefix_length' => 2,
    'operator' => 'AND'
])
    ->where('is_published', true)
    ->get();
```

You may find and adjust default query type and options for each query type in config (Queries section).

2) Sorting with `orderBy()` method:

```
$articles = Article::search($keywords)
            ->orderBy('id', 'desc')
            ->get();
```

3) A separate Elasticsearch index for each model.

If you have defined several indexes in your config (config/elasticsearch.php), 
you may choose which index a model belongs to by overriding `searchableWithin()` method in your model:

```
public function searchableWithin()
{
    return 'foobar';
}
```

Please note: if you do not override `searchableWithin()` in your model, the first index from the config will be used.

## Credits

- [Erick Tamayo](https://github.com/ericktamayo)
- [Thomas Jensen](https://github.com/thomasjsn)
- [All Contributors](../../contributors)

## License

The MIT License (MIT).