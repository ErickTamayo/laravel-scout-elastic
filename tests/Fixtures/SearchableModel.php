<?php

namespace Tamayo\LaravelScoutElastic\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class SearchableModel extends Model
{
  use Searchable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['id'];

  public function getIdAttribute()
  {
    return 1;
  }

  public function searchableAs()
  {
    return 'table';
  }

  public function scoutMetadata()
  {
    return [];
  }

  public function toSearchableArray()
  {
    return ['id' => 1];
  }
}
