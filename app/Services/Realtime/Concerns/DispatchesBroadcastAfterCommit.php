<?php

declare(strict_types=1);

namespace App\Services\Realtime\Concerns;

use Illuminate\Support\Facades\DB;

trait DispatchesBroadcastAfterCommit
{
    protected function afterCommitBroadcast(callable $callback): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($callback);

            return;
        }

        $callback();
    }
}
