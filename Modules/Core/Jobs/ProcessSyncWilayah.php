<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Modules\Core\Helpers\ServiceReturn;
use Modules\Core\Services\DatabaseDistributionService;
use Modules\Core\Services\WilayahManagementService;

class ProcessSyncWilayah implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private array $client
    )
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DatabaseDistributionService::switchClient($this->client);

        Log::channel('queue')->info('DB v1 CONNECTION', [
            "config" => config('database.connections.siakadv1')
        ]);

        Log::channel('queue')->info('DB v2 CONNECTION', [
            "config" => config('database.connections.pgsql')
        ]);
        
        // sync all negara
        $result = (new WilayahManagementService())->syncNegaraFromSiakadV1(isInitSync: true);

        if (!ServiceReturn::isError($result)) {
            // sync all provinsi, kab/kota, dan kecamatan
            $result = (new WilayahManagementService())->syncAllFromSiakadV1(isInitSync: true);
        }

        $err = false;
        if (ServiceReturn::isError($result)) {
            $err = true;
            $message = ServiceReturn::getError($result, 'message');
        } else {
            [, $message] = ServiceReturn::getValue($result);
        }

        if ($err) {
            Log::channel('queue')->error('Failed Sync: Core - ProcessSyncWilayah', [
                'message' => $message,
            ]);
        }
    }

    /**
     * Handle final failed job.
     *
     * @param \Throwable $e
     * @return void
     */
    public function failed(\Throwable $e): void
    {
        Log::channel('queue')->error('Failed Sync: Core - ProcessSyncWilayah', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
