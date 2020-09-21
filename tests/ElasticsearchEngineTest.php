<?php

namespace Tamayo\LaravelScoutElastic\Tests;

use Mockery;
use Mockery\MockInterface;
use Elasticsearch\Client;
use Laravel\Scout\Builder;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Tamayo\LaravelScoutElastic\Engines\ElasticsearchEngine;
use Tamayo\LaravelScoutElastic\Tests\Fixtures\SearchableModel;

class ElasticsearchEngineTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_update_adds_objects_to_index()
    {
        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('bulk')->with([
            'body' => [
                [
                    'update' => [
                        '_id' => 1,
                        '_index' => 'table',
                        '_type' => 'Tamayo\LaravelScoutElastic\Tests\Fixtures\SearchableModel',
                    ]
                ],
                [
                    'doc' => ['id' => 1],
                    'doc_as_upsert' => true
                ]
            ]
        ]);

        $engine = new ElasticsearchEngine($client);
        $engine->update(Collection::make([new SearchableModel]));
    }

    public function test_delete_removes_objects_to_index()
    {
        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('bulk')->with([
            'body' => [
                [
                    'delete' => [
                        '_id' => 1,
                        '_index' => 'table',
                        '_type' => 'Tamayo\LaravelScoutElastic\Tests\Fixtures\SearchableModel',
                    ]
                ],
            ]
        ]);

        $engine = new ElasticsearchEngine($client);
        $engine->delete(Collection::make([new SearchableModel]));
    }

    public function test_search_sends_correct_parameters_to_elasticsearch()
    {
        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('search')->with([
            'index' => 'table',
            'type' => 'Tamayo\LaravelScoutElastic\Tests\Fixtures\SearchableModel',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['query_string' => ['query' => '*zonda*']],
                            ['match_phrase' => ['foo' => 1]],
                            ['terms' => ['bar' => [1, 3]]],
                        ]
                    ]
                ],
                'sort' => [
                    ['id' => 'desc']
                ]
            ]
        ]);

        $engine = new ElasticsearchEngine($client);
        $builder = new Builder(new SearchableModel, 'zonda');
        $builder->where('foo', 1);
        $builder->where('bar', [1, 3]);
        $builder->orderBy('id', 'desc');
        $engine->search($builder);
    }

    public function test_builder_callback_can_manipulate_search_parameters_to_elasticsearch()
    {
        /** @var Client|MockInterface $client */
        $client = Mockery::mock(\Elasticsearch\Client::class);
        $client->shouldReceive('search')->with(['modified_by_callback']);

        $engine = new ElasticsearchEngine($client);
        $builder = new Builder(
            new SearchableModel(),
            'huayra',
            function (Client $client, $query, $params) {
                $this->assertNotEmpty($params);
                $this->assertEquals('huayra', $query);
                $params = ['modified_by_callback'];

                return $client->search($params);
            }
        );

        $engine->search($builder);
    }

    public function test_map_correctly_maps_results_to_models()
    {
        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $engine = new ElasticsearchEngine($client);

        /** @var Builder|MockInterface $builder */
        $builder = Mockery::mock(Builder::class);

        /** @var Model|MockInterface $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getScoutKey')->andReturn('1');
        $model->shouldReceive('getScoutModelsByIds')->once()->with($builder, ['1'])->andReturn($models = Collection::make([$model]));
        $model->shouldReceive('newCollection')->andReturn($models);

        $results = $engine->map($builder, [
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

    public function test_map_correctly_maps_sort_results()
    {
        /** @var Client|MockInterface $client */
        $client = Mockery::mock(Client::class);
        $engine = new ElasticsearchEngine($client);

        /** @var Builder|MockInterface $builder */
        $builder = Mockery::mock(Builder::class);

        /** @var Model|MockInterface $model */
        $secondModel = Mockery::mock(Model::class);
        $secondModel->shouldReceive('getScoutKey')->andReturn('2');

        /** @var Model|MockInterface $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getScoutKey')->andReturn('1');
        $model->shouldReceive('getScoutModelsByIds')->once()->with($builder, ['2', '1'])->andReturn($models = Collection::make([$model, $secondModel]));
        $model->shouldReceive('newCollection')->andReturn($models);

        $results = $engine->map($builder, [
            'hits' => [
                'total' => '2',
                'hits' => [
                    [
                        '_id' => '2'
                    ],
                    [
                        '_id' => '1'
                    ]
                ]
            ]
        ], $model);
        $this->assertEquals($secondModel, $results[0]);
        $this->assertEquals($model, $results[1]);
    }
}
