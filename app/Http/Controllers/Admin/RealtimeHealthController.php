<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class RealtimeHealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $broadcastDriver = (string) config('broadcasting.default', 'log');
        $reverbConfigured = $broadcastDriver === 'reverb';

        $pendingJobs = 0;
        $oldestPendingSeconds = null;
        $queueHealthy = true;

        if (Schema::hasTable('jobs')) {
            $queues = ['default', 'notifications', 'notifications-email'];
            $pendingQuery = DB::table('jobs')
                ->whereIn('queue', $queues)
                ->whereNull('reserved_at');

            $pendingJobs = (int) $pendingQuery->count();

            $stale = (clone $pendingQuery)
                ->where('created_at', '<=', now()->subSeconds(30))
                ->orderBy('created_at')
                ->first();

            if ($stale !== null) {
                $queueHealthy = false;
                $oldestPendingSeconds = (int) now()->diffInSeconds($stale->created_at, true);
            }
        }

        $fallbackMode = ! $reverbConfigured || ! $queueHealthy;
        $mode = $fallbackMode
            ? 'fallback'
            : ($pendingJobs > 5 ? 'degraded' : 'live');

        return response()->json([
            'websocket_connected' => false,
            'reverb_configured' => $reverbConfigured,
            'queue_healthy' => $queueHealthy,
            'fallback_mode' => $fallbackMode,
            'oldest_pending_seconds' => $oldestPendingSeconds,
            'pending_jobs' => $pendingJobs,
            'mode' => $mode,
            'checked_at' => now()->toIso8601String(),
        ]);
    }
}
