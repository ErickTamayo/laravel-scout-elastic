<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch host
    |--------------------------------------------------------------------------
    |
    */

    'hosts' => [
        env('ELASTICSEARCH_HOST', 'http://localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queries and query parameters
    |--------------------------------------------------------------------------
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
    | searchableWithin() method.
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
