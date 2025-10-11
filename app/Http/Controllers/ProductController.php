<?php

namespace App\Http\Controllers;

use App\Interfaces\ProductInterface;
use App\Models\Product;
use App\Traits\ResponseApi;


class ProductController extends Controller
{
  use ResponseApi;
  protected ProductInterface $productService;

  public function __construct(ProductInterface $productService)
  {
    $this->productService = $productService;
  }

  public function show(Product $product)
  {
    try {
      $product = $this->productService->getProductById($product->id);
      return $this->success($product, 'Product retrieved successfully', 200);
    } catch (\Exception $e) {
      return $this->error($e->getMessage(), 400);
    }
  }
}
