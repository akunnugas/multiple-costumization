<?php

namespace Modules\Litabmas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Extensions\Models\IndonesianModel;
use Modules\Litabmas\Database\factories\AspekPenilaianKomposisiProposalFactory;
use Modules\Litabmas\Enums\JenisPendanaanEnum;

class AspekPenilaianKomposisiProposal extends IndonesianModel
{
    use SoftDeletes, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'litabmas.aspek_penilaian_komposisi_proposal';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id_periode_pendanaan',
        'kode_jenis_pendanaan',
        'nama_komposisi_proposal',
        'bobot_komposisi_proposal',
        'no',
    ];

    /**
     * Model field validation.
     *
     * @var array<array>
     */
    const RULES = [
        'id_periode_pendanaan' => ['required' => true, 'options' => PeriodePendanaan::class],                                // Periode Pendanaan
        'kode_jenis_pendanaan' => ['required' => true, 'options' => JenisPendanaanEnum::CODES],                              // Jenis Pendanaan
        'nama_komposisi_proposal' => ['required' => true, 'maxlength' => 255],                                               // Nama Aspek Penilaian
        'bobot_komposisi_proposal' => ['required' => true, 'type' => 'numeric'],                                             // Bobot
        'no' => ['required' => true, 'type' => 'numeric'],                                                                   // No
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


    /**
     * Utk validasi unique composite.
     * @return array[]
     */
    protected static function uniqueColumns(): array
    {
        return [
            'uniqueNo' => [
                'defaultMessage' => __('validation.unique', [
                    'attribute' => __('litabmas::aspek_penilaian_komposisi_proposal.no')
                ]),
                'fields' => ['id_periode_pendanaan', 'kode_jenis_pendanaan', 'no']
            ],
            'uniqueNamaKomposisiProposal' => [
                'defaultMessage' => __('validation.unique', [
                    'attribute' => __('litabmas::aspek_penilaian_komposisi_proposal.nama_komposisi_proposal')
                ]),
                'fields' => ['id_periode_pendanaan', 'kode_jenis_pendanaan', 'nama_komposisi_proposal']
            ]
        ];
    }

    /**
     * @return AspekPenilaianKomposisiProposalFactory
     */
    protected static function newFactory()
    {
        return AspekPenilaianKomposisiProposalFactory::new();
    }

    /**
     * relasi ke penilaian reviewer komposisi proposal
     */
    public function penilaianReviewerKomposisiProposal(){
        return $this->hasMany(PenilaianReviewerKomposisiProposal::class, 'id_aspek_penilaian_komposisi_proposal');
    }
}
