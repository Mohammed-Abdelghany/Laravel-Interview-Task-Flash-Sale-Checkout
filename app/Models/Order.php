<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
  /** @use HasFactory<\Database\Factories\OrdersFactory> */
  use HasFactory;
  protected $fillable = [
    'hold_id',
    'product_id',
    'status',
    'amount',
  ];
  public function hold()
  {
    return $this->belongsTo(Hold::class);
  }
  public function product()
  {
    return $this->hold->product();
  }
}
