<?php

namespace Modules\Core\Services;

use Illuminate\Console\View\Components\Error;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Error as HelpersError;
use Modules\Core\Models\Shared\Klien;

class DatabaseDistributionService
{
    /**
     * Migrate to all client database.
     *
     * @param mixed $outputBuffer
     * @param bool $isFresh
     */
    public static function migrate($outputBuffer, $isFresh = false, $tenant = null)
    {
        $cmd = 'migrate';
        if ($isFresh) {
            $cmd .= ':fresh';
        }

        if (empty($tenant)) {
            $return = static::runToAllDatabase(function ($client) use ($cmd, $outputBuffer) {
                $outputBuffer->info("Migrating database... " . $client['kode_klien'] . " - " .$client['nama_klien']);
                Artisan::call($cmd, [], $outputBuffer);
            });
        } else {
            $return = static::runToSpecificTenant($tenant, function () use ($cmd, $outputBuffer) {
                Artisan::call($cmd, [], $outputBuffer);
            });
        }

        if (HelpersError::isError($return)) {
            $error = new Error($outputBuffer);
            $error->render($return->message);
        }

        if (empty($return)) {
            $error = new Error($outputBuffer);
            $error->render('Client tidak ditemukan.');
        }
    }

    /**
     * Migrate to shared database.
     *
     * @param mixed $outputBuffer
     * @param bool $isFresh
     */
    public static function migrateToShared($outputBuffer, $isFresh)
    {
        $cmd = 'migrate';
        if ($isFresh) {
            $cmd .= ':fresh';
        }

        Artisan::call($cmd . ' --database=shared --path="Modules/Core/Database/Migrations/Shared"', [], $outputBuffer);
    }

    /**
     * Rollback to all client database.
     *
     * @param $outputBuffer
     * @param int $rollback
     * @return void
     */
    public static function rollback($outputBuffer, int $rollback, $tenant = null)
    {
        if (empty($tenant)) {
            $return = static::runToAllDatabase(function () use ($outputBuffer, $rollback) {
                Artisan::call('migrate:rollback', ['--step' => $rollback], $outputBuffer);
            });
        } else {
            $return = static::runToSpecificTenant($tenant, function () use ($outputBuffer, $rollback) {
                Artisan::call('migrate:rollback', ['--step' => $rollback], $outputBuffer);
            });
        }

        if (empty($return)) {
            $error = new Error($outputBuffer);
            $error->render('Client tidak ditemukan.');
        }
    }

    /**
     * Switch client database.
     *
     * @param array $client
     */
    public static function switchClient($client)
    {
        // asumsi sama seperti klien cache
        if (empty(request()->client)) {
            request()->merge(['client' => $client]);
        }

        $key = config('database.default');
        $config = config('database.connections.' . $key);
        $configV1 = config('database.connections.siakadv1');

        $newConfig = [
            'database' => $client['nama_db'],
            'username' => $client['username_db'],
            'password' => $client['password_db'],
            'timezone' => $client['timezone'] ?? config('app.timezone'),
        ];

        $newV1Config = [
            'database' => $client['nama_db_v1'] ?? null,
            'username' => $client['username_db_v1'] ?? null,
            'password' => $client['password_db_v1'] ?? null,
        ];

        Config::set('database.connections.' . $key, $newConfig + $config);
        Config::set('database.connections.siakadv1', $newV1Config + $configV1);

        DB::purge($key);
        DB::purge('siakadv1');
    }

    /**
     * Run callback function to all databases.
     *
     * @param callable $callback
     *
     * @return array
     */
    public static function runToAllDatabase(callable $callback)
    {
        // cek client
        $clients = Klien::join('klien_config', 'klien_config.id_klien', '=', 'klien.id')
            ->select('klien.*', 'klien_config.timezone', 'klien_config.nama_db', 'klien_config.username_db', 'klien_config.password_db', 'klien_config.nama_db_v1', 'klien_config.username_db_v1', 'klien_config.password_db_v1')
            ->get()->toArray();

        if (empty($clients)) {
            return [[
                'client' => null,
                'return' => $callback()
            ]];
        }

        $return = [];
        foreach ($clients as $client) {
            static::switchClient($client);

            $return[] = [
                'client' => $client,
                'return' => $callback($client),
            ];
        }

        return $return;
    }

    /**
     * Run callback function to specific tenant.
     *
     * @param array $clientCodes
     * @param callable $callback
     * @return array|\Modules\Core\Helpers\Error
     */
    public static function runToSpecificTenant(array $clientCodes, callable $callback)
    {
        // cek client
        $clients = Klien::join('klien_config', 'klien_config.id_klien', '=', 'klien.id')
            ->select(
                'klien.*',
                'klien_config.timezone',
                'klien_config.nama_db',
                'klien_config.username_db',
                'klien_config.password_db',
                'klien_config.nama_db_v1',
                'klien_config.username_db_v1',
                'klien_config.password_db_v1'
            )
            ->whereIn('kode_klien', $clientCodes)
            ->get()->toArray();

        // cek hasil $clients, yg ada di $clientCods tapi tidak ada di $clients
        $notFound = array_diff($clientCodes, array_column($clients, 'kode_klien'));
        if (!empty($notFound)) {
            return new \Modules\Core\Helpers\Error('Klien tidak ditemukan: ' . implode(', ', $notFound));
        }

        $return = [];
        foreach ($clients as $client) {
            static::switchClient($client);

            $return[] = [
                'client' => $client,
                'return' => $callback($client),
            ];
        }

        return $return;
    }
}
