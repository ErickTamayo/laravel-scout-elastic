<?php

use Illuminate\Database\Eloquent\Collection;
use ScoutEngines\Elasticsearch\ElasticsearchEngine;

class ElasticsearchEngineTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_update_adds_objects_to_index()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('bulk')->with([
            'body' => [
                [
                    'update' => [
                        '_id' => 1,
                        '_index' => 'scout',
                        '_type' => 'table',
                    ]
                ],
                [
                    'doc' => ['id' => 1 ],
                    'doc_as_upsert' => true
                ]
            ]
        ]);

        $engine = new ElasticsearchEngine($client, 'scout');
        $engine->update(Collection::make([new ElasticsearchEngineTestModel]));
    }

    public function test_delete_removes_objects_to_index()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('bulk')->with([
            'body' => [
                [
                    'delete' => [
                        '_id' => 1,
                        '_index' => 'scout',
                        '_type' => 'table',
                    ]
                ],
            ]
        ]);

        $engine = new ElasticsearchEngine($client, 'scout');
        $engine->delete(Collection::make([new ElasticsearchEngineTestModel]));
    }

    public function test_search_sends_correct_parameters_to_elasticsearch()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $client->shouldReceive('search')->with([
            'index' => 'scout',
            'type' => 'table',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['query_string' => ['query' => '*zonda*']],
                            ['match_phrase' => ['foo' => 1]]
                        ]
                    ]
                ],
                'sort' => [
                    ['id' => 'desc']
                ]
            ]
        ]);

        $engine = new ElasticsearchEngine($client, 'scout');
        $builder = new Laravel\Scout\Builder(new ElasticsearchEngineTestModel, 'zonda');
        $builder->where('foo', 1);
        $builder->orderBy('id', 'desc');
        $engine->search($builder);
    }

    public function test_map_correctly_maps_results_to_models()
    {
        $client = Mockery::mock('Elasticsearch\Client');
        $engine = new ElasticsearchEngine($client, 'scout');

        $model = Mockery::mock('Illuminate\Database\Eloquent\Model');
        $model->shouldReceive('getKeyName')->andReturn('id');
        $model->shouldReceive('whereIn')->once()->with('id', ['1'])->andReturn($model);
        $model->shouldReceive('get')->once()->andReturn(Collection::make([new ElasticsearchEngineTestModel]));

        $results = $engine->map([
            'hits' => [
                'total' => '1',
                'hits' => [
                    [
                        '_id' => '1'
                    ]
                ]
            ]
        ], $model);

        $this->assertEquals(1, count($results));
    }
}

class ElasticsearchEngineTestModel extends \Illuminate\Database\Eloquent\Model
{
    public function getIdAttribute()
    {
        return 1;
    }

    public function searchableAs()
    {
        return 'table';
    }

    public function getKey()
    {
        return '1';
    }

    public function toSearchableArray()
    {
        return ['id' => 1];
    }
}
