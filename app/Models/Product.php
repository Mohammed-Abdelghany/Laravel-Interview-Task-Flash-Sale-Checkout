<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  /** @use HasFactory<\Database\Factories\ProductsFactory> */
  use HasFactory;
  protected $fillable = ['name', 'price', 'total_stock'];
  public function holds()
  {
    return $this->hasMany(Hold::class);
  }
}
