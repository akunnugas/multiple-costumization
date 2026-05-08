<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Database\factories\AspekPenilaianPresentasiProposalFactory;
use Modules\Litabmas\Enums\JenisPendanaanEnum;

class AspekPenilaianPresentasiProposal extends IndonesianModel
{
    use SoftDeletes, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.aspek_penilaian_presentasi_proposal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_periode_pendanaan',
        'kode_jenis_pendanaan',
        'pertanyaan_presentasi_proposal',
        'bobot_pertanyaan_presentasi_proposal',
        'no',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_periode_pendanaan' => ['required' => true, 'options' => PeriodePendanaan::class],                                          // Periode Pendanaan
        'kode_jenis_pendanaan' => ['required' => true, 'options' => JenisPendanaanEnum::CODES],                                        // Jenis Pendanaan
        'pertanyaan_presentasi_proposal' => ['required' => true],                                                                      // Pertanyaan
        'bobot_pertanyaan_presentasi_proposal' => ['required' => true, 'type' => 'numeric', 'max' => 100],                             // Bobot
        'no' => ['required' => true, 'type' => 'numeric'],                                                                             // No
    ];

    const NILAI_MEMENUHI_KRITERIA = 300;
    const TIDAK_BAIK = 100;
    const KURANG_BAIK = 200;
    const CUKUP = 300;
    const BAIK = 400;
    const SANGAT_BAIK = 500;

    const OPTION_SKALA_BOBOT = [
        self::TIDAK_BAIK => 'Tidak Baik',
        self::KURANG_BAIK => 'Kurang Baik',
        self::CUKUP => 'Cukup',
        self::BAIK => 'Baik',
        self::SANGAT_BAIK => 'Sangat Baik',
    ];

    public static function getOptionsBobotPenilaianPresentasi()
    {
        return [
            self::TIDAK_BAIK => 'Tidak Baik',
            self::KURANG_BAIK => 'Kurang Baik',
            self::CUKUP => 'Cukup',
            self::BAIK => 'Baik',
            self::SANGAT_BAIK => 'Sangat Baik',
        ];
    }

    /**
     * Utk validasi unique composite.
     * @return array[]
     */
    protected static function uniqueColumns(): array
    {
        return [
            'uniqueNo' => [
                'defaultMessage' => __('validation.unique', [
                    'attribute' => __('litabmas::aspek_penilaian_presentasi_proposal.no')
                ]),
                'fields' => ['id_periode_pendanaan', 'kode_jenis_pendanaan', 'no']
            ],
            'uniquePertanyaanPresentasiProposal' => [
                'defaultMessage' => __('validation.unique', [
                    'attribute' => __('litabmas::aspek_penilaian_presentasi_proposal.pertanyaan_presentasi_proposal')
                ]),
                'fields' => ['id_periode_pendanaan', 'kode_jenis_pendanaan', 'pertanyaan_presentasi_proposal']
            ]
        ];
    }

    /**
     * @return AspekPenilaianPresentasiProposalFactory
     */
    protected static function newFactory()
    {
        return AspekPenilaianPresentasiProposalFactory::new();
    }

    /**
     * relasi ke penilaian reviewer presentasi proposal
     */

    public function penilaianReviewerPresentasiProposal(){
        return $this->hasMany(PenilaianReviewerPresentasiProposal::class, 'id_aspek_penilaian_presentasi_proposal');
    }
}
