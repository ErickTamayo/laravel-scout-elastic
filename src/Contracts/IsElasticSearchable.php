<?php

namespace ScoutEngines\Elasticsearch\Contracts;

interface IsElasticSearchable
{
    public static function search($query, $callback = null);

    public static function elasticSearch($method, $query, array $params = null);

    public function searchable();

    public function unsearchable();

    public function searchableWithin();

    public function searchableAs();

    public function toSearchableArray();
}
