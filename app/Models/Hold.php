<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
  /** @use HasFactory<\Database\Factories\HoldsFactory> */
  use HasFactory;
  protected $fillable = [
    'product_id',
    'quantity',
    'expires_at',
    'used',
  ];
  protected $casts = [
    'expires_at' => 'datetime',
    'used' => 'boolean',
  ];
  public function product()
  {
    return $this->belongsTo(Product::class);
  }
  public function order()
  {
    return $this->hasOne(Order::class);
  }
}
