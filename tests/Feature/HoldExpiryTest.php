<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\HoldService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class HoldExpiryTest extends TestCase
{
  public function test_hold_expiry_returns_availability()
  {
    $product = Product::create([
      'name' => 'Expire Product',
      'price' => 5.00,
      'total_stock' => 2,
    ]);

    $service = new HoldService();

    $hold = $service->createHold(['product_id' => $product->id, 'qty' => 2]);

    // reserved should be 2
    $this->assertEquals(2, $product->holds()->where('used', false)->sum('quantity'));

    // Fast-forward time by running releaseExpiredHolds after manually expiring holds
    // We'll set expires_at in DB to past to simulate expiry
    foreach ($product->holds as $h) {
      $h->expires_at = now()->subMinutes(5);
      $h->save();
    }

    $released = $service->releaseExpiredHolds();
    $this->assertEquals(1, $released);

    $this->assertEquals(0, $product->holds()->where('used', false)->sum('quantity'));
  }
}
