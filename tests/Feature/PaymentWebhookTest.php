<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Order;
use App\Services\HoldService;
use App\Services\OrderService;
use App\Services\PaymentWebhookService;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
  public function test_webhook_idempotency_and_before_order()
  {
    $product = Product::create([
      'name' => 'Webhook Product',
      'price' => 20.00,
      'total_stock' => 1,
    ]);

    $holdService = new HoldService();
    $orderService = new OrderService();
    $webhookService = new PaymentWebhookService();

    $hold = $holdService->createHold(['product_id' => $product->id, 'qty' => 1]);

    $key = 'idemp-1234';

    // Send webhook before order exists
    $res1 = $webhookService->process(['idempotency_key' => $key, 'order_id' => 9999, 'status' => 'success']);
    $this->assertEquals('pending_order_creation', $res1['status']);

    // Create order from hold
    $orderResult = $orderService->createOrder(['hold_id' => $hold['hold_id']]);
    $orderId = $orderResult['order_id'];

    // Now send webhook again with same key — should be treated as duplicate
    $res2 = $webhookService->process(['idempotency_key' => $key, 'order_id' => $orderId, 'status' => 'success']);
    $this->assertEquals('duplicate', $res2['status']);

    // Send a fresh webhook for the created order
    $key2 = 'idemp-5678';
    $res3 = $webhookService->process(['idempotency_key' => $key2, 'order_id' => $orderId, 'status' => 'success']);
    $this->assertEquals('processed', $res3['status']);

    $order = Order::find($orderId);
    $this->assertEquals('paid', $order->status);
  }
}
