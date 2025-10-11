<?php

namespace App\Http\Controllers;

use App\Models\payment_webhooks;
use App\Http\Requests\Storepayment_webhooksRequest;
use App\Http\Requests\Updatepayment_webhooksRequest;

class PaymentWebhooksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storepayment_webhooksRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(payment_webhooks $payment_webhooks)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(payment_webhooks $payment_webhooks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatepayment_webhooksRequest $request, payment_webhooks $payment_webhooks)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(payment_webhooks $payment_webhooks)
    {
        //
    }
}
