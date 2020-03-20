<?php

namespace DealerInspire\Operations;

use Carbon\Carbon;

final class Operator
{
    public function queue()
    {
        foreach (config('operations.operations') as $operation) {
            $operation::whereNull('started_run_at')->where('should_run_at', '<=', Carbon::now())->get()->each(function (Operation $operation) {
                $operation->started_run_at = Carbon::now();
                $operation->save();

                if (method_exists($operation, 'queue')) {
                    $operation->queue();
                }

                if (property_exists($operation, 'queue')) {
                    OperationJob::dispatch($operation)->onQueue($operation->queue);
                } else {
                    OperationJob::dispatch($operation);
                }
            });
        }
    }
}
