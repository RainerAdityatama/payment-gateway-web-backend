<?php

namespace App\Http\Controllers;

use App\Services\MidtransWebhookService;
use Exception;

class MidtransWebhookController extends Controller
{
    protected MidtransWebhookService $midtransWebhookService;

    public function __construct(MidtransWebhookService $midtransWebhookService)
    {
        $this->midtransWebhookService = $midtransWebhookService;
    }

    public function handleWebhook()
    {
        try {
            $this->midtransWebhookService->handle();

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook berhasil diproses'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
