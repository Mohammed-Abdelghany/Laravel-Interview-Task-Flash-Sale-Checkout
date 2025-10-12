<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentWebhook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentWebhookService
{
  /**
   * Check if webhook with the same idempotency key already processed
   */
  public function isDuplicate(string $key): bool
  {
    return Cache::has("webhook:$key") ||
      PaymentWebhook::where('idempotency_key', $key)->exists();
  }

  /**
   * Process webhook safely (idempotent + race condition safe)
   */
  public function process(array $data): array
  {
    return DB::transaction(function () use ($data) {
      $key = $data['idempotency_key'];

      if ($this->isDuplicate($key)) {
        Log::info("Duplicate webhook ignored", ['key' => $key]);
        return ['status' => 'duplicate'];
      }
      $order = Order::lockForUpdate()->find($data['order_id']);
      if (!$order) {
        PaymentWebhook::create([
          'order_id' => null,
          'idempotency_key' => $key,
          'status' => $data['status'],
        ]);
        Log::warning("Webhook arrived before order created", ['key' => $key]);
        return ['status' => 'pending_order_creation'];
      }
      PaymentWebhook::create([
        'order_id' => $order->id,
        'idempotency_key' => $key,
        'status' => $data['status'],
      ]);
      if ($data['status'] === 'success') {
        $order->update(['status' => 'paid']);
      } elseif ($data['status'] === 'failure') {
        $order->update(['status' => 'cancelled']);
        $order->hold?->update(['used' => false]);
      }
      Cache::put("webhook:$key", true, now()->addDay());
      Log::info("Webhook processed successfully", [
        'order_id' => $order->id,
        'status' => $data['status'],
      ]);

      return [
        'status' => 'processed',
        'order_id' => $order->id,
        'final_state' => $order->status,
      ];
    });
  }

}
