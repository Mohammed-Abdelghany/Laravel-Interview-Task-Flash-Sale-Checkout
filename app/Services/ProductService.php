<?php

namespace App\Services;

use App\Interfaces\ProductInterface;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductService implements ProductInterface
{
  public function getProductById($id)
  {
    $cacheKey = "product:{$id}:available_stock";
    return Cache::remember($cacheKey, now()->addSeconds(5), function () use ($id) {
      $product = Product::findOrFail($id);
      $reservedQty = $product->holds()
        ->where('expires_at', '>', now())
        ->where('used', false)
        ->sum('quantity');

      $available = max(0, $product->total_stock - $reservedQty);
      return [
        'id' => $product->id,
        'name' => $product->name,
        'price' => $product->price,
        'available_stock' => $available,
      ];
    });
  }
}
