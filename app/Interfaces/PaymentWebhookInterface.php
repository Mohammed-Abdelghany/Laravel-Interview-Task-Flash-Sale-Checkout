<?php

namespace App\Interfaces;

interface PaymentWebhookInterface
{
    public function handleWebhook(array $data): void;
}
