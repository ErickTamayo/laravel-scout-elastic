<?php

namespace ScoutEngines\Elasticsearch;

use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder as ElasticBuilder;

class ElasticsearchProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        EngineManager::extend('elasticsearch', function($app){
            return new ElasticsearchEngine(ElasticBuilder::create()
                ->setHosts(config('scout.elastic.hosts'))
                ->build(),
                config('scout.elasticsearch.index')
            );
        });
    }
}