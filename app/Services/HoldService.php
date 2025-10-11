<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Exception;

class HoldService
{
  public function createHold(array $data): array
  {
    return DB::transaction(function () use ($data) {

      $product = Product::where('id', $data['product_id'])
        ->lockForUpdate()
        ->firstOrFail();

      $reservedQty = $product->holds()
        ->where('expires_at', '>', now())
        ->where('used', false)
        ->sum('quantity');

      $available = $product->total_stock - $reservedQty;

      if ($available < $data['qty']) {
        throw new Exception('Not enough stock available.');
      }
      $hold = Hold::create([
        'product_id' => $data['product_id'],
        'quantity' => $data['qty'],
        'expires_at' => now()->addMinutes(2),
        'used' => false,
      ]);
      return [
        'hold_id' => $hold->id,
        'expires_at' => $hold->expires_at->toDateTimeString(),
      ];
    });
  }



  public function releaseExpiredHolds(): int
  {
    $now = Carbon::now();

    return DB::transaction(function () use ($now) {
      $expiredHolds = Hold::where('expires_at', '<', $now)
        ->where('used', false)
        ->get();
      foreach ($expiredHolds as $hold) {

        $hold->delete();
      }
      return $expiredHolds->count();
    });
  }
}
