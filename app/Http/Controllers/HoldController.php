<?php

namespace App\Http\Controllers;

use App\Services\HoldService;
use App\Traits\ResponseApi;
use Illuminate\Http\Request;
use Exception;

class HoldController extends Controller
{
  use ResponseApi;

  protected HoldService $holdService;

  public function __construct(HoldService $holdService)
  {
    $this->holdService = $holdService;
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'product_id' => 'required|exists:products,id',
      'qty'   => 'required|integer|min:1',
    ]);

    try {
      $hold = $this->holdService->createHold($data);

      return $this->success([
        'hold_id'     => $hold['hold_id'],
        'expires_at'  => $hold['expires_at'],
      ], 'Hold created successfully', 201);
    } catch (Exception $e) {
      return $this->error($e->getMessage(), 400);
    }
  }
}
