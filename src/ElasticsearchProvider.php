<?php

namespace ScoutEngines\Elasticsearch;

use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;
use Elasticsearch\ClientBuilder as ElasticBuilder;
use ScoutEngines\Elasticsearch\Console\ElasticIndicesCommand;
use ScoutEngines\Elasticsearch\Console\ElasticMakeIndicesCommand;

class ElasticsearchProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        resolve(EngineManager::class)->extend('elasticsearch', function($app) {
            return new ElasticsearchEngine(ElasticBuilder::create()
                ->setHosts(config('elasticsearch.hosts'))
                ->build(),
                config('elasticsearch.queries')
            );
        });
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ElasticIndicesCommand::class,
                ElasticMakeIndicesCommand::class
            ]);

            $this->publishes([
                __DIR__ . '/../config/elasticsearch.php' => config_path('elasticsearch.php'),
            ]);
        }
    }
}
