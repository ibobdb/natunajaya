<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'type',
    'last_kir_check',
  ];
  public function car()
  {
    return $this->belongsTo(\App\Models\Car::class);
  }
}
