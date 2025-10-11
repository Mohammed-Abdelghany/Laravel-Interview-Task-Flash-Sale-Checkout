<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderService
{
  public function createOrder(array $data)
  {

    return DB::transaction(function () use ($data) {

      $hold = Hold::lockForUpdate()->findOrFail($data['hold_id']);

      if ($hold->used) {
        throw new \Exception('Hold already used.');
      }
      if ($hold->expires_at < Carbon::now()) {
        throw new \Exception('Hold has expired.');
      }

      $order = Order::create([
        'hold_id' => $hold->id,
        'product_id' => $hold->product_id,
        'quantity' => $hold->quantity,
        'amount' => $hold->product->price * $hold->quantity,
        'status' => 'pending',
      ]);

      $hold->update(['used' => true]);

      return [
        'order_id' => $order->id,
        'status' => $order->status,
        'amount' => $order->amount,
      ];
    });
  }
}
