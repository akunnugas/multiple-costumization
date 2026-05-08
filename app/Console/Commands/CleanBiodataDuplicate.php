<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\DatabaseDistributionService;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Biodata;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CleanBiodataDuplicate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:clean-biodata-duplicate {--tenant=}';

    /**
     * The console command description.
     */
    protected $description = 'Clean Duplicated Biodata Pegawai.';

    /**
     * The context of the command.
     *
     * @var string
     */
    private string $context = 'Biodata';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['tenant', null, InputOption::VALUE_OPTIONAL, 'Tenant(s) to migrate, separated by comma.'],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // get tenant option and split by comma
        $tenantOption = $this->option('tenant');
        $tenants = $tenantOption ? explode(',', $tenantOption) : [];

        // run to all tenant
        if (empty($tenants)) {
            $this->components->info("Start Migrating Data $this->context To All Database");

            DatabaseDistributionService::runToAllDatabase(function () {
                $this->cleanData();
            });
        } else { // run to specific tenant
            $this->components->info("Start Migrating Data $this->context To Specific Database");

            $response = DatabaseDistributionService::runToSpecificTenant($tenants, function () {
                $this->cleanData();
            });

            if (Error::isError($response)) {
                $message = $response?->message;
                $this->components->error($message);
                return CommandAlias::FAILURE;
            }
        }

        // finish
        $this->components->info("Finish Migrating Data $this->context");
        return CommandAlias::SUCCESS;
    }

    /**
     * Synchronize universitas data.
     *
     * @param int|null $limit
     * @return int
     */
    private function cleanData()
    {
        $dataInformation = DB::select("
            SELECT
                tc.table_schema,
                tc.table_name,
                kcu.column_name
            FROM
                information_schema.table_constraints AS tc
            JOIN
                information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name
            WHERE
                kcu.column_name in ('id_biodata', 'id_personil');
        ");

        $fkList = collect($dataInformation)
            ->map(function ($row) {
                return [
                    "schema" => $row->table_schema,
                    "table" => $row->table_name,
                    "column" => $row->column_name
                ];
            })
            ->toArray();

        DB::beginTransaction();
        try {
            $grouped = Biodata::all()->groupBy("ref_key_pegawai");
            $beforeCount = Biodata::count();

            foreach ($grouped as $ref => $items) {
                if ($items->count() <= 1) {
                    continue;
                }

                $toKeep = $items->first();
                $toDelete = $items->slice(1);

                foreach ($toDelete as $biodata) {
                    foreach ($fkList as $fk) {
                        $fullTable = "{$fk["schema"]}.{$fk["table"]}";
                        $column = $fk["column"];

                        // Cek apakah ada row yang refer ke biodata ini
                        $rows = DB::table($fullTable)
                            ->where($column, $biodata->id)
                            ->get();

                        if ($rows->count() > 0) {
                            // Update ke ID yang disimpan
                            DB::table($fullTable)
                                ->where($column, $biodata->id)
                                ->update([$column => $toKeep->id]);
                        }
                    }

                    // Setelah semua FK sudah diganti → aman untuk delete
                    $biodata->forceDelete();
                }
            }

            $afterCount = Biodata::count();
            echo "Pembersihan data biodata berhasil\n";
            echo "Total biodata sebelum: {$beforeCount}, sesudah: {$afterCount}";
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Terjadi error: " . $e->getMessage();
        }
    }
}
