<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;
use Exception;

class CheckoutController extends Controller
{
    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function process(CheckoutRequest $request)
    {
        try {
            $validated = $request->validated();

            $checkoutProggres = $this->checkoutService->processCheckout($validated);

            return response()->json([
                'status' => 'success',
                'data' => $checkoutProggres
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
