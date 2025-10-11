<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Traits\ResponseApi;

class OrderController extends Controller
{
  use ResponseApi;

  protected OrderService $orderService;

  public function __construct(OrderService $orderService)
  {
    $this->orderService = $orderService;
  }
  public function store(Request $request)
  {


    try {
      $data = $request->validate([
        'hold_id' => 'required|exists:holds,id',
      ]);
      $order = $this->orderService->createOrder($data);
      return $this->success($order, 'Order created successfully', 201);
    } catch (\Exception $e) {
      return $this->error($e->getMessage(), 400);
    }
  }
}
