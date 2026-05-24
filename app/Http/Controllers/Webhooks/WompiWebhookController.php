<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentWebhookProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WompiWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentWebhookProcessor $processor): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();
        $checksum = $request->header('X-Event-Checksum');

        $processor->handle('wompi', $payload, is_string($checksum) ? $checksum : null, $request);

        return response()->json(['ok' => true]);
    }
}
