<?php

namespace Modules\Litabmas\Http\Controllers\Report;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\SiakadV1;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Models\SumberPendanaan;
use Modules\Litabmas\Services\PendanaanKegiatanService;

class PendanaanKegiatan extends Controller
{
    public function listColumn() {
        $data = [];
        $data['id_periode_pendanaan'] = ['required' => true, 'label' => 'Periode', 'type' => 'select', 'options' => PeriodePendanaan::class];
        $data['id_sumber_pendanaan'] = ['required' => true, 'label' => 'Sumber Pendanaan', 'variant' => 'search', 'options' => SumberPendanaan::class];
        $data['id_klaster_pendanaan'] = ['required' => false, 'label' => 'Klaster Pendanaan', 'variant' => 'search', 'options' => KlasterPendanaan::class];

        return $data;
    }

    public function gReport(Request $request)
    {
        // validation dynamic from data
        WebRequest::customValidate($request->all(), $this->listColumn());

        $service = new PendanaanKegiatanService();

        $title = 'Laporan Monitoring Pendanaan';
        $isUsingKop = $request->has('is_kop') && $request->is_kop;
        $data = $service->getDaftarProposal($request->id_sumber_pendanaan, $request->id_klaster_pendanaan)->toArray();

        foreach ($data as $item){
            foreach ($item as $key => $value) {
                if ($key == 'nama_anggota') {
                    $listAnggota = explode(',', $value);

                    $namaKetua = null;
                    $newAnggota = [];
                    foreach ($listAnggota as $anggota) {
                        if (strpos($anggota, '(Ketua)') !== false) {
                            $namaKetua = trim(str_replace('(Ketua)', '', $anggota));
                        } else {
                            $newAnggota[] = trim($anggota);
                        }
                    }
                    if (!empty($newAnggota)) {
                        $htmlAnggota = '';
                        foreach ($newAnggota as $anggota) {
                            $htmlAnggota .= '- ' . $anggota . '<br/>';
                        }
                        $htmlAnggota .= '';
                        $newAnggota = $htmlAnggota;
                    }

                    $item->ketua = $namaKetua;
                    $item->{$key} = $newAnggota;
                }

                if ($key == 'nominal_anggaran_disetujui') {
                    $item->{$key} = (float) $value;
                }
            }
        }

        $rekap = $service->show($request->id_sumber_pendanaan)->toArray();
        $rekap = (object) $rekap;

        $klaster = KlasterPendanaan::find($request->id_klaster_pendanaan);
        $columns = [
            ['field' => 'no', 'label' => 'No'],
            ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
            ['field' => 'judul_proposal', 'label' => 'Judul Proposal'],
            ['field' => 'ketua', 'label' => 'Ketua'],
            ['field' => 'nama_anggota', 'label' => 'Anggota'],
            ['field' => 'nominal_anggaran_disetujui', 'label' => 'Biaya Disetujui', 'currency_field' => 'mata_uang'],
        ];

        // get data univ
        $dataUniv = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        $v1Siakad = new SiakadV1;
        $dataUnivV1 = $v1Siakad->send('select email,alamat,telepon from ref.ms_unit where idunit = ?', [$dataUniv->ref_key_siakad])[1][0] ?? [];
        $dataUnivV1['nama'] = $dataUniv->nama_unit;

        $header['Periode Pendanaan'] = $rekap->periode;
        $header['Sumber Pendanaan'] = $rekap->nama_sumber_pendanaan;
        $header['Pengelola Bantuan'] = $rekap->pengelola_bantuan ?? '-';
        $header['Total Pendanaan'] = money($rekap->total_pendanaan);
        $header['Dana Diberikan'] = money($rekap->dana_diberikan ?? 0);
        $header['Dana Tersisa'] = money($rekap->dana_tersisa ?? 0);
        $header['Proposal Diterima'] = $rekap->total_proposal;

        return view('litabmas::pages.reports.result', compact('title', 'header', 'columns', 'data', 'isUsingKop', 'dataUnivV1'));
    }
}

