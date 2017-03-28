<?php

namespace ScoutEngines\Elasticsearch\Console;

use Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;

class ElasticMakeIndicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:make-indices {index?} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make ElasticSearch indices defined in config file, with mapping';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $host = config('elasticsearch.hosts');

        $client = ClientBuilder::create()->setHosts($host)->build();

        $indices = ! is_null($this->argument('index')) ?
            [$this->argument('index')] : array_keys(config('elasticsearch.indices'));

        foreach ($indices as $index) {

            $indexConfig = config("elasticsearch.indices.{$index}");

            if(is_null($indexConfig)) {
                $this->error("Config for index \"{$index}\" not found, skipping...");
                continue;
            }

            // Delete index if it already exists
            if ($client->indices()->exists(['index' => $index])) {
                if ($this->option('force') || $this->confirm("Index \"{$index}\" exists, delete and recreate?")) {
                    $this->warn("Index \"{$index}\" exists, deleting!");
                    $client->indices()->delete(['index' => $index]);
                } else {
                    $this->line("Skipping index: \"{$index}\"");
                    continue;
                }
            }

            // Create index with settings from config file
            $this->info("Creating index: {$index}");
            $client->indices()->create([
                'index' => $index,
                'body' => [
                    "settings" => $indexConfig['settings']
                ]
            ]);

            if (! isset($indexConfig['mappings'])) {
                continue;
            }

            foreach ($indexConfig['mappings'] as $type => $mapping) {

                // Create mapping for type, from config file
                $this->info("- Creating mapping for: {$type}");
                $client->indices()->putMapping([
                    'index' => $index,
                    'type' => $type,
                    'body' => [
                        'properties' => $mapping
                    ]
                ]);
            }

        }

    }

}
