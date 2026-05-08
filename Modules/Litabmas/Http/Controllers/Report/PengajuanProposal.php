<?php

namespace Modules\Litabmas\Http\Controllers\Report;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\SiakadV1;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\UnitKerja;
use Modules\Litabmas\Enums\StatusAgendaKegiatanEnum;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
use Modules\Litabmas\Models\PeriodePendanaan;

class PengajuanProposal extends Controller
{
    public function listColumn()
    {
        $data = [];

        $data['id_periode_pendanaan'] = ['required' => true, 'label' => 'Periode', 'type' => 'select', 'options' => PeriodePendanaan::class];
        $data['status_agenda'] = ['required' => false, 'label' => 'Status Tahapan Kegiatan', 'options' => StatusAgendaKegiatanEnum::FILTER_AGENDA];
        $data['status_seleksi'] = ['required' => false, 'label' => 'Status Seleksi', 'options' => StatusAgendaKegiatanEnum::FILTER_SELEKSI];

        return $data;
    }

    public function gReport(Request $request)
    {
        // validation dynamic from data
        WebRequest::customValidate($request->all(), $this->listColumn());

        $title = 'Laporan Pengajuan Proposal';
        $isUsingKop = $request->has('is_kop') && $request->is_kop;

        $dataUniv = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();

        $v1Siakad = new SiakadV1;
        $dataUnivV1 = $v1Siakad->send('select email,alamat,telepon from ref.ms_unit where idunit = ?', [$dataUniv->ref_key_siakad])[1][0] ?? [];
        $dataUnivV1['nama'] = $dataUniv->nama_unit;

        $status_agenda = $request->status_agenda ?? '';
        $status_seleksi = $request->status_seleksi ?? '';
        $kolom_status = '';
        switch ($status_agenda) {
            case StatusAgendaKegiatanEnum::ADMINISTRASI:
                if ($status_seleksi === StatusAgendaKegiatanEnum::LOLOS) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI;
                } else if ($status_seleksi === StatusAgendaKegiatanEnum::TIDAK_LOLOS) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI;
                } else if ($status_seleksi === StatusAgendaKegiatanEnum::PROSES) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI;
                }
                break;
            case StatusAgendaKegiatanEnum::NOMINASI:
                if ($status_seleksi === StatusAgendaKegiatanEnum::LOLOS) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI;
                } else if ($status_seleksi === StatusAgendaKegiatanEnum::TIDAK_LOLOS) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI;
                } else if ($status_seleksi === StatusAgendaKegiatanEnum::PROSES) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL8_NOMINASI_BELUM_DITINJAU;
                }
                break;
            case StatusAgendaKegiatanEnum::PENDANAAN:
                if ($status_seleksi === StatusAgendaKegiatanEnum::LOLOS) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN;
                } else if ($status_seleksi === StatusAgendaKegiatanEnum::TIDAK_LOLOS) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN;
                } else if ($status_seleksi === StatusAgendaKegiatanEnum::PROSES) {
                    $kolom_status = PengajuanPendanaanStatus2::LEVEL10_BELUM_PENENTUAN_PENDANAAN;
                }
                break;
            default:
                abort(404);
                break;
        }

        $columns = [
            ['field' => 'no', 'label' => 'No'],
            ['field' => 'judul_penelitian', 'label' => 'Judul Proposal'],
            ['field' => 'nama_klaster', 'label' => 'Klaster Pendanaan'],
            ['field' => 'ketua', 'label' => 'Ketua'],
            ['field' => 'status_agenda_kegiatan', 'label' => 'Status Proposal'],
            ['field' => 'nominal_anggaran_diajukan', 'label' => 'Usulan Biaya', 'currency_field' => 'mata_uang'],
        ];

        $data = PengajuanPendanaan::join('litabmas.sumber_pendanaan', 'litabmas.sumber_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan.id_sumber_pendanaan')
            ->join('litabmas.klaster_pendanaan', 'litabmas.klaster_pendanaan.id', '=', 'litabmas.pengajuan_pendanaan.id_klaster_pendanaan')
            ->join('litabmas.pengajuan_pendanaan_anggota', function ($join) {
                $join->on('litabmas.pengajuan_pendanaan_anggota.id_pengajuan_pendanaan', '=', 'litabmas.pengajuan_pendanaan.id')
                    ->where('litabmas.pengajuan_pendanaan_anggota.apakah_ketua', true);
            })
            ->join('core.biodata', 'core.biodata.id', '=', 'litabmas.pengajuan_pendanaan_anggota.id_biodata')
            ->select('litabmas.pengajuan_pendanaan.*', 'litabmas.klaster_pendanaan.nama_klaster', 'core.biodata.nama as ketua')
            ->when(!empty($kolom_status), function ($query) use ($kolom_status) {
                return $query->where('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', $kolom_status);
            })
            ->when(empty($kolom_status), function ($query) use ($status_agenda) {
                if ($status_agenda === StatusAgendaKegiatanEnum::ADMINISTRASI) {
                    return $query->whereIn('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', [
                        PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI,
                        PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI,
                        PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI
                    ]);
                } else if ($status_agenda === StatusAgendaKegiatanEnum::NOMINASI) {
                    return $query->whereIn('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', [
                        PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI,
                        PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI,
                        PengajuanPendanaanStatus2::LEVEL8_NOMINASI_BELUM_DITINJAU
                    ]);
                } else if ($status_agenda === StatusAgendaKegiatanEnum::PENDANAAN) {
                    return $query->whereIn('litabmas.pengajuan_pendanaan.status_agenda_kegiatan', [
                        PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN,
                        PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN,
                        PengajuanPendanaanStatus2::LEVEL10_BELUM_PENENTUAN_PENDANAAN
                    ]);
                }
            })
            ->where('litabmas.sumber_pendanaan.id_periode_pendanaan', $request->id_periode_pendanaan)
            ->get();

        $n_lolos = $n_tidak_lolos = $n_proses = 0;

        if (empty($kolom_status)) {
            foreach ($data as $index => $value) {
                if ($status_agenda === StatusAgendaKegiatanEnum::ADMINISTRASI) {
                    if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI) {
                        $n_lolos++;
                    } else if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI) {
                        $n_tidak_lolos++;
                    } else if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL5_PROSES_SELEKSI_ADMINISTRASI) {
                        $n_proses++;
                    }
                } else if ($status_agenda === StatusAgendaKegiatanEnum::NOMINASI) {
                    if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI) {
                        $n_lolos++;
                    } else if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI) {
                        $n_tidak_lolos++;
                    } else if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL8_NOMINASI_BELUM_DITINJAU) {
                        $n_proses++;
                    }
                } else if ($status_agenda === StatusAgendaKegiatanEnum::PENDANAAN) {
                    if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN) {
                        $n_lolos++;
                    } else if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN) {
                        $n_tidak_lolos++;
                    } else if ($value->status_agenda_kegiatan === PengajuanPendanaanStatus2::LEVEL10_BELUM_PENENTUAN_PENDANAAN) {
                        $n_proses++;
                    }
                }

                $data[$index]->status_agenda_kegiatan = PengajuanPendanaanStatus2::getLabelStatus($value->status_agenda_kegiatan);
            }
        }

        $header['Periode Pendanaan'] = PeriodePendanaan::find($request->id_periode_pendanaan)->tahun;
        $header['Tahapan Kegiatan Seleksi'] = $status_agenda ? StatusAgendaKegiatanEnum::FILTER_AGENDA[$request->status_agenda] : 'Semua Status';
        $header['Status Seleksi'] = $request->status_seleksi ? StatusAgendaKegiatanEnum::FILTER_SELEKSI[$request->status_seleksi] : 'Semua Status';

        if (empty($kolom_status)) {
            $header['Proses '  . ucfirst($status_agenda)] = $n_proses;
            $header['Lolos ' . ucfirst($status_agenda)] = $n_lolos;
            $header['Tidak Lolos ' . ucfirst($status_agenda)] = $n_tidak_lolos;
        }

        return view('litabmas::pages.reports.result', compact('title', 'header', 'columns', 'data', 'isUsingKop', 'dataUnivV1'));
    }
}
