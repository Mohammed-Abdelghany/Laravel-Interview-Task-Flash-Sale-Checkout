<?php

namespace App\Http\Controllers;

use App\Services\PaymentWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class PaymentWebhookController extends Controller
{
  protected PaymentWebhookService $webhookService;

  public function __construct(PaymentWebhookService $webhookService)
  {
    $this->webhookService = $webhookService;
  }

  public function handle(Request $request): JsonResponse
  {
    try {
      if (! $this->verifySignature($request)) {
        Log::warning('Invalid webhook signature', ['headers' => $request->headers->all()]);
        return response()->json(['message' => 'Invalid signature'], 403);
      }
      $idempotencyKey = $request->header('Idempotency-Key');
      if (! $idempotencyKey) {
        return response()->json(['message' => 'Missing Idempotency-Key header'], 400);
      }
      if ($this->webhookService->isDuplicate($idempotencyKey)) {
        Log::info('Duplicate webhook ignored', ['key' => $idempotencyKey]);
        return response()->json(['message' => 'Already processed'], 200);
      }
      $data = $request->only(['order_id', 'transaction_id', 'status', 'idempotency_key']);
      $data['idempotency_key'] = $idempotencyKey;

      $result = $this->webhookService->process($data);

      return response()->json([
        'message' => 'Webhook processed successfully',
        'data' => $result,
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

      Log::warning('Webhook arrived before order creation', [
        'payload' => $request->all()
      ]);
      return response()->json([
        'message' => 'Order not found yet, will retry later'
      ], 202);
    } catch (\Throwable $e) {
      Log::error('Webhook processing failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      return response()->json(['message' => 'Internal server error'], 500);
    }
  }
  public function verifySignature(Request $request): bool
  {
    $signature = $request->header('X-Webhook-Signature');
    $timestamp = $request->header('X-Webhook-Timestamp');

    if (!$signature || !$timestamp) {
      return false;
    }

    if (abs(time() - (int)$timestamp) > 300) {
      return false;
    }
    $payload = $request->getContent();
    $secret = config('services.payment.webhook_secret', env('PAYMENT_WEBHOOK_SECRET'));

    $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    return hash_equals($expectedSignature, $signature);
  }
}