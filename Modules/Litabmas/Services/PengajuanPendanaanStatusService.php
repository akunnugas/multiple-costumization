<?php

namespace Modules\Litabmas\Services;

use Modules\Litabmas\Models\PengajuanPendanaanStatus;
use Modules\Litabmas\Models\PeriodePendanaan;

class PengajuanPendanaanStatusService
{
    /**
     * @var PeriodePendanaan
     */
    protected $model = PengajuanPendanaanStatus::class;

    /**
     * Init service.
     */
    public function __construct()
    {
        $this->model = new PengajuanPendanaanStatus;
    }

    /**
     * Get status penentuan pendanaan
     *
     * @param int $idPengajuanPendanaan
     * @return string|null
     */
    public function getStatusPenentuanPendanaan(int $idPengajuanPendanaan)
    {
        return PengajuanPendanaanStatus::where('id_pengajuan_pendanaan', $idPengajuanPendanaan)
            ->select('status_penentuan_pendanaan')
            ->first()?->status_penentuan_pendanaan;
    }
}
