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
You must have a Elasticsearch server up and running, indices can be created with an Artisan command; see below.

After you've published the Laravel Scout package configuration:

```php
// config/scout.php
// Set your driver to elasticsearch
    'driver' => env('SCOUT_DRIVER', 'elasticsearch'),
```

## Usage

### Creating Elasticsearch indexes with proper mapping

You may define custom mappings for Elasticsearch fields in the config. See examples in the [config file](config/elasticsearch.php).
If you prefer storing mappings in models, you may create a static public method `mapping()` in each particular model:

```php
class Article extends Model
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
And then use it in the config file:
```php
 'indices' => [

    'realestate' => [
        'settings' => [
            "number_of_shards" => 1,
            "number_of_replicas" => 0,
        ],
        'mappings' => [
            'articles' => \App\Article::mapping(),
        ],
    ],
 ]
```
The document type, in this example `articles` must match `searchableAs()` for the respective model.

Elasticsearch can set default types to model fields on the first insert if you do not explicitly define them. 
However; sometimes the defaults are not what you're looking for, or you need to define additional mapping properties.

In that case, we strongly recommend creating indices with proper mappings before inserting any data.
For that purpose, there is an Artisan command, called `elastic:make-indices {index}` which creates an index based on
the settings in your configuration file.

To create all indices from your config just ignore the `{index}` parameter and run:

```
php artisan elastic:make-indices
```

If the index exists you will be asked if you want to delete and recreate it, or you can use the `--force` flag.

To get information about your existing Elasticsearch indices you may want to use the following command:

```
php artisan elastic:indices
```

### Indexing data

You may follow instructions from the [official Laravel Scout documentation](https://laravel.com/docs/5.3/scout)
to index your data.

### Search

The package supports everything that is provided by Laravel Scout.

The Scout `search` method used the default query method defined in the config file.

Sorting with `orderBy()` method:

```php
$articles = Article::search($keywords)
            ->orderBy('id', 'desc')
            ->get();
```

#### Elastic specific

However, to use the extra Elasticsearch features included in this package, use trait `ElasticSearchable` 
by adding it to your model instead of `Searchable`:

```php
class Article extends Model
{
    // use Searchable;
    use ElasticSearchable;
    // ...
}
```

The package features:
 
1) The `elasticSearch` method, `elasticSearch($method, $query, array $params = null)`:

```php
$articles = Article::elasticSearch('multi_match', $q, [
    'fields' => ['title', 'content', 'tags'],
    'fuzziness' => 'auto',
    'prefix_length' => 2,
    'operator' => 'AND'
])->get();
```

Parameters are taken from the configuration, for the specific query method, if not supplied. But you may override them.

2) A separate Elasticsearch index for each model.

If you have defined several indices in your [config file](config/elasticsearch.php), 
you may choose which index a model belongs to by overriding `searchableWithin()` method in your model:

```php
public function searchableWithin()
{
    return 'foobar';
}
```

If you do not override `searchableWithin()` in your model, the first index from the config will be used.

## Credits

- [Erick Tamayo](https://github.com/ericktamayo)
- [Thomas Jensen](https://github.com/thomasjsn)
- [All Contributors](../../contributors)

## License

The MIT License (MIT).
