<?php

namespace ScoutEngines\Elasticsearch\Console;

use Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;

class ElasticIndicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:indices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show Elasticsearch indices (cat command)';

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

        $indices = $client->cat()->indices();

        if(count($indices) > 0) {
            $headers = array_keys(current($indices));
            $this->table($headers, $indices);
        } else {
            $this->warn('No indices found.');
        }

    }

}
