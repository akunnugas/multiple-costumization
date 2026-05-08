<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\SPMI\Models\AkreditasiPeringkat;
use Modules\SPMI\Models\PenilaianPanduan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $lamdikPeringkat = [
            [
                'kode_peringkat' => 'TT',
                'nama_peringkat_akreditasi' => 'Tidak Terakreditasi',
                'nilai_minimal' => 0,
                'nilai_maksimal' => 200,
            ],
            [
                'kode_peringkat' => 'TA',
                'nama_peringkat_akreditasi' => 'Terakreditasi',
                'nilai_minimal' => 201,
                'nilai_maksimal' => 321,
            ],
            [
                'kode_peringkat' => 'T3',
                'nama_peringkat_akreditasi' => 'Terakreditasi Unggul 3 Tahun',
                'nilai_minimal' => 322,
                'nilai_maksimal' => 500,
            ]
        ];

        $lamteknikPeringkat = [
            [
                'kode_peringkat' => 'TT',
                'nama_peringkat_akreditasi' => 'Tidak Terakreditasi',
                'nilai_minimal' => 0,
                'nilai_maksimal' => 199,
            ],
            [
                'kode_peringkat' => 'TA',
                'nama_peringkat_akreditasi' => 'Terakreditasi',
                'nilai_minimal' => 200,
                'nilai_maksimal' => 330,
            ],
            [
                'kode_peringkat' => 'T3',
                'nama_peringkat_akreditasi' => 'Terakreditasi Unggul 3 Tahun',
                'nilai_minimal' => 331,
                'nilai_maksimal' => 500,
            ]
        ];

        $laminfokomPeringkat = [
            [
                'kode_peringkat' => 'TT',
                'nama_peringkat_akreditasi' => 'Tidak Terakreditasi',
                'nilai_minimal' => 0,
                'nilai_maksimal' => 199,
            ],
            [
                'kode_peringkat' => 'TA',
                'nama_peringkat_akreditasi' => 'Terakreditasi',
                'nilai_minimal' => 200,
                'nilai_maksimal' => 320,
            ],
            [
                'kode_peringkat' => 'TA3',
                'nama_peringkat_akreditasi' => 'Terakreditasi Unggul 3 Tahun',
                'nilai_minimal' => 321,
                'nilai_maksimal' => 360,
            ],
            [
                'kode_peringkat' => 'TA5',
                'nama_peringkat_akreditasi' => 'Terakreditasi Unggul 5 Tahun',
                'nilai_minimal' => 361,
                'nilai_maksimal' => 500,
            ]
        ];

        $lamdikPenilaianPanduan = PenilaianPanduan::where('kode_penilaian_panduan', 'LAMDIK-S1-3.0')
            ->where('apakah_data_default', true)
            ->first();
        if ($lamdikPenilaianPanduan) {
            foreach ($lamdikPeringkat as $peringkat) {
                AkreditasiPeringkat::updateOrCreate(
                    [
                        'kode_peringkat' => $peringkat['kode_peringkat'],
                        'id_penilaian_panduan' => $lamdikPenilaianPanduan->id,
                    ],
                    [
                        'nama_peringkat_akreditasi' => $peringkat['nama_peringkat_akreditasi'],
                        'nilai_minimal' => $peringkat['nilai_minimal'],
                        'nilai_maksimal' => $peringkat['nilai_maksimal'],
                        'deskripsi' => null,
                    ]
                );
            }
        }

        $lamteknikPenilaianPanduan = PenilaianPanduan::where('kode_penilaian_panduan', 'LAMTEKNIKS1-25')
            ->where('apakah_data_default', true)
            ->first();
        if ($lamteknikPenilaianPanduan) {
            foreach ($lamteknikPeringkat as $peringkat) {
                AkreditasiPeringkat::updateOrCreate(
                    [
                        'kode_peringkat' => $peringkat['kode_peringkat'],
                        'id_penilaian_panduan' => $lamteknikPenilaianPanduan->id,
                    ],
                    [
                        'nama_peringkat_akreditasi' => $peringkat['nama_peringkat_akreditasi'],
                        'nilai_minimal' => $peringkat['nilai_minimal'],
                        'nilai_maksimal' => $peringkat['nilai_maksimal'],
                        'deskripsi' => null,
                    ]
                );
            }
        }

        $laminfokomPenilaianPanduan = PenilaianPanduan::where('kode_penilaian_panduan', 'INFOKOM2.1S1')
            ->where('apakah_data_default', true)
            ->first();
        if ($laminfokomPenilaianPanduan) {
            foreach ($laminfokomPeringkat as $peringkat) {
                AkreditasiPeringkat::updateOrCreate(
                    [
                        'kode_peringkat' => $peringkat['kode_peringkat'],
                        'id_penilaian_panduan' => $laminfokomPenilaianPanduan->id,
                    ],
                    [
                        'nama_peringkat_akreditasi' => $peringkat['nama_peringkat_akreditasi'],
                        'nilai_minimal' => $peringkat['nilai_minimal'],
                        'nilai_maksimal' => $peringkat['nilai_maksimal'],
                        'deskripsi' => null,
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
