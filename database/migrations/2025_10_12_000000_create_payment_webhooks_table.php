<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('payment_webhooks', function (Blueprint $table) {
      $table->id();
      $table->string('idempotency_key')->unique();
      $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
      $table->enum('status', ['pending', 'success', 'failure'])->nullable();
      $table->timestamps();
      $table->index('idempotency_key');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('payment_webhooks');
  }
};
