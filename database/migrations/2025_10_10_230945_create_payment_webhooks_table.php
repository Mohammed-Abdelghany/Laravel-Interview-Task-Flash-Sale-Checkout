<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('webhook_logs', function (Blueprint $table) {
      $table->id();
      $table->string('idempotency_key')->unique();
      $table->foreignId('order_id')->constrained()->onDelete('cascade');
      $table->string('transaction_id');
      $table->enum('status', ['processing', 'processed', 'duplicate', 'failed']);
      $table->json('payload');
      $table->timestamp('processed_at')->nullable();
      $table->timestamps();

      $table->index(['order_id', 'status']);
      $table->index('created_at');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('webhook_logs');
  }
};
