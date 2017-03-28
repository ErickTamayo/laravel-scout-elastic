<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch host
    |--------------------------------------------------------------------------
    |
    | Your Elasticsearch servers go here, by default it will use localhost. But
    | you can change that here or in your environment file.
    */

    'hosts' => [
        env('ELASTICSEARCH_HOST', 'http://localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queries and query parameters
    |--------------------------------------------------------------------------
    |
    | Here you can specify different search methods, and their parameters.
    | The scout "search" method uses the default query type, with its parameters.
    | If you use the "elasticSearch" method you can specify the query type and
    | override the search parameters when performing the search.
    |
    */

    'queries' => [
        'default' => 'query_string',

        'query_string' => [
            'default_operator' => "AND"
        ],
        'multi_match' => [
            'fields' => '_all',
            'fuzziness' => 'auto'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch indices
    |--------------------------------------------------------------------------
    |
    | Here you can define your indices, with separate settings and mappings.
    | You can choose which index a model belongs to my overriding the
    | searchableWithin() method. A model will, by default, belong to the first
    | index listed here.
    |
    | You may specify your mappings in the model if you like that approach,
    | just make a static method, e.g. mapping() and refer to it here, like:
    |
    | 'mappings' => [
    |     'articles' => \App\Article::mapping()
    | ]
    |
    */

    'indices' => [

        'laravel' => [
            'settings' => [
                "number_of_shards" => 1,
                "number_of_replicas" => 0,
            ],
            'mappings' => [
                'articles' => [
                    'title' => [
                        'type' => 'string'
                    ]
                ]
            ]
        ],

        'another_index' => [
            'settings' => [
                "number_of_shards" => 1,
                "number_of_replicas" => 0,
            ],
            'mappings' => [
                'articles' => [
                    'title' => [
                        'type' => 'string'
                    ]
                ]
            ]
        ]

    ]

];
