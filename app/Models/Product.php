<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  /** @use HasFactory<\Database\Factories\ProductsFactory> */
  use HasFactory;
  public function holds()
  {
    return $this->hasMany(Hold::class);
  }
}
