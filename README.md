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

Now you can use Laravel Scout as described in the [official documentation](https://laravel.com/docs/5.3/scout)

However; to use the extra Elasticsearch features included in this package, use trait `ElasticSearchable`.
This includes everything from Scout, plus a few extras, like the `elasticSearch` method:

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

You may also choose which index a model belongs to by overriding `searchableWithin()`:

```
public function searchableWithin()
{
    return 'foobar';
}
```

## Credits

- [Erick Tamayo](https://github.com/ericktamayo)
- [Thomas Jensen](https://github.com/thomasjsn)
- [All Contributors](../../contributors)

## License

The MIT License (MIT).