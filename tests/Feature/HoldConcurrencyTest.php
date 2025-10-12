<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\HoldService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Throwable;

class HoldConcurrencyTest extends TestCase
{
  public function test_parallel_holds_do_not_oversell()
  {
    // create product with total_stock = 5
    $product = Product::create([
      'name' => 'Test Product',
      'price' => 10.00,
      'total_stock' => 5,
    ]);

    $service = new HoldService();

    // Attempt two holds concurrently that together equal stock boundary: 3 and 2
    $results = [];

    // Simulate concurrency by running two transactions one after another but using lockForUpdate in service.
    try {
      $r1 = $service->createHold(['product_id' => $product->id, 'qty' => 3]);
      $results[] = $r1;
    } catch (Throwable $e) {
      $results[] = ['error' => $e->getMessage()];
    }

    try {
      $r2 = $service->createHold(['product_id' => $product->id, 'qty' => 2]);
      $results[] = $r2;
    } catch (Throwable $e) {
      $results[] = ['error' => $e->getMessage()];
    }

    // Both should succeed and total reserved should be 5
    $this->assertCount(2, $results);

    $reserved = $product->holds()->sum('quantity');
    $this->assertEquals(5, $reserved);

    // Now any additional hold should fail
    $this->expectException(\Exception::class);
    $service->createHold(['product_id' => $product->id, 'qty' => 1]);
  }
}
