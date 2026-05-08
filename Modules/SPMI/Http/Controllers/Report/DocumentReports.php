<?php

namespace Modules\SPMI\Http\Controllers\Report;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\SiakadV1;
use Modules\Core\Helpers\WebRequest;
use Modules\Core\Models\Biodata;
use Modules\Core\Models\Model;
use Modules\Core\Models\Pegawai;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AkreditasiBuku;
use Modules\SPMI\Models\PenilaianAudit;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\JadwalAuditUnit;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Models\PengisianIndikator;
use Modules\SPMI\Models\SuratTugasAuditor;
use Modules\SPMI\Models\SuratTugasAuditorPegawai;
use Modules\SPMI\Services\AuditTemuanManagementService;
use Modules\SPMI\Services\DashboardService;
use Modules\SPMI\Services\HasilAkhirAuditManagementService;
use Modules\SPMI\Services\PenilaianAuditorManagementService;
use Modules\SPMI\Services\PengisianIndikatorManagementService;
use Modules\SPMI\Services\PengisianIndikatorEvaluasiDiriManagementService;
use Modules\SPMI\Services\TinjauanTemuanManagementService;

class DocumentReports extends Controller
{

    public function listColumn()
    {
        // FIXME: add format to config to dynamic
        $format = [
            'html' => 'HTML',
            // 'pdf' => 'PDF',
            // 'excel' => 'Excel',
        ];

        $data = [];
        $data['period'] = ['required' => true, 'label' => 'Periode AMI', 'type' => 'select', 'options' => AuditPeriode::class];
        $data['unit'] = ['required' => true, 'label' => 'Unit Kerja', 'variant' => 'search', 'type' => 'select', 'options' => UnitKerja::optionByType([UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true)];
        $data['id_jadwal_audit'] = ['required' => true, 'label' => 'Nama Kegiatan AMI', 'type' => 'select', 'options' => []];
        $data['type'] = ['required' => true, 'label' => 'Jenis Laporan', 'type' => 'select', 'options' => $this->listReport()];
        if (count($format) > 1) {
            $data['academic_year'] = ['label' => 'Format', 'type' => 'select', 'options' => $format];
        }
        $data['using_kop'] = ['label' => 'Gunakan Kop', 'control' => 'checkbox', 'choiceLabel' => 'Menggunakan KOP Laporan'];
        $data['pageback'] = ['type' => 'hidden'];

        return $data;
    }

    /**
     * Report List Question (REPP)
     */
    public function show()
    {
        $title = 'Cetak Dokumen SPMI';

        $data = $this->listColumn();

        return view('spmi::pages.reports.show', compact('title', 'data'));
    }

    /**
     * Generate Reports
     */
    public function gReport(Request $request)
    {
        // validation dynamic from data
        WebRequest::customValidate($request->all(), $this->listColumn());

        // search report type
        switch ($request->type) {
            case 'plk':
                return $this->gFillingReport($request);
                break;
            case 'pled':
                return $this->gFillingSelfEvaluation($request);
                break;
            case 'ptk':
                return $this->gLaporanPTK($request);
            case 'temuan':
                return $this->gLaporanTemuan($request);
            case 'komentar':
                return $this->gListQuestion($request);
                break;
            default:
                return Error::returnValue('Tipe laporan tidak ditemukan');
                break;
        }
    }

    /**
     * Generate List Question Report
     */
    public function gListQuestion(Request $request)
    {
        $informations = [];
        $filter = $request->all();

        $yearData = AuditPeriode::find($request->period);

        $data = PenilaianAudit::where([
            'id_unit' => $request->unit,
            'id_audit_periode' => $request->period,
            'id_jadwal_audit' => $request->id_jadwal_audit,
            'is_finalized' => true
        ])->first();

        if (!$data) {
            $assessmentMatrices = [];
        } else {
            $assessmentMatrices = (new PenilaianAuditorManagementService)
                ->showAllMatricesByPenilaianPanduan($data->assessment_guide_id, $data->id_unit, $data->id_audit_periode, $data->id, $data->id_jadwal_audit);

            $organization = UnitKerja::showInformation($request->unit);
            $personAuditor = SuratTugasAuditor::getAuditorByPeriodAndUnit($request->period, $request->unit);

            $informations['Hari/Tanggal Audit'] = Cstr::dateNow();
            $informations['Nama Kegiatan AMI'] = JadwalAudit::find($request->id_jadwal_audit)?->nama_jadwal_audit;
            $informations['Periode AMI'] = $yearData->year;
            $informations['Fakultas/Departemen'] = $organization[UnitKerja::FACULTY]['nama_unit'];
            $informations['Program Pendidikan'] = $data->studyProgram->degree->name;
            $informations['Program Studi'] = $data->studyProgram->name;
            $informations['Ketua Program Studi'] = ['value' => $data->studyProgram->leader->person->name];

            foreach ($personAuditor as $key => $value) {
                if ($value->posisi == SuratTugasAuditorPegawai::POSITION_LEAD) {
                    $informations['Ketua Tim Auditor'] = ['value' => $value->name];
                } else if ($value->posisi == SuratTugasAuditorPegawai::POSITION_MEMBER) {
                    $informations['Anggota Tim Auditor ' . $key] = $value->name;
                }
            }
        }
        $pageback = $request['pageback'] ?? false;

        return view('spmi::pages.reports.question-report', [
            'filter' => $filter,
            'assessmentMatrices' => $assessmentMatrices,
            'informations' => $informations,
            'isHasAsign' => true,
            'pageback' => $pageback
        ]);
    }

    /**
     * Generate Filling Indicator Report
     */
    public function gFillingReport(Request $request)
    {
        ini_set('memory_limit', '256M');

        if ((empty($request['period']) || empty($request['unit'])) && !empty($request['self'])) {
            $request['period'] = $request['self']['id_audit_periode'];
            $request['unit'] = $request['self']['id_unit'];
        }

        $idPeriod = $request['period'] ?? null;
        $idUnit = $request['unit'] ?? null;
        $idJadwalAudit = $request['id_jadwal_audit'] ?? null;

        // get jadwal audit unit
        $jadwalUnit = JadwalAuditUnit::where([
            'id_jadwal_audit' => $idJadwalAudit,
            'id_unit' => $idUnit
        ])->first();
        $idPengisianPanduan = $jadwalUnit->id_pengisian_panduan ?? null;
        if (empty($idPengisianPanduan)) {
            return view('spmi::pages.reports.404');
        }

        $fillingIndicator = PengisianIndikator::where([
            'id_unit' => $idUnit,
            'id_audit_periode' => $idPeriod,
            'id_pengisian_panduan' => $idPengisianPanduan,
            'id_jadwal_audit' => $idJadwalAudit,
            'jenis_indikator' => AkreditasiBuku::PERFORMANCE_REPORT
        ])->first();
        if (empty($fillingIndicator)) {
            return view('spmi::pages.reports.404');
        }

        $fillingIndicatorService = new PengisianIndikatorManagementService;
        $data = $fillingIndicatorService->showReport($fillingIndicator->id_pengisian_panduan, $idPeriod, $idUnit);
        $auditPeriod = AuditPeriode::find($idPeriod);
        $studyProgram = UnitKerja::find($idUnit);

        $PengisianPanduan = PengisianPanduan::find($fillingIndicator->id_pengisian_panduan);
        $title = $PengisianPanduan->name;

        $information = [];
        $information['audit'] = $auditPeriod;
        $information['nama_jadwal_audit'] = JadwalAudit::find($idJadwalAudit)?->nama_jadwal_audit;
        $information['study_program'] =  $studyProgram;
        $information['study_program_name'] =  $studyProgram->jenjangPendidikan->kode_jenjang . ' ' . $studyProgram->nama_unit;
        $information['id_pengisian_indikator'] = $fillingIndicator->id;
        $information['kode_panduan'] = $PengisianPanduan->kode_pengisian_panduan;
        $information['apakah_data_default'] = $PengisianPanduan->apakah_data_default;
        $pageback = $request['pageback'] ?? false;

        return view('spmi::pages.reports.filling-indicator-report', compact('data', 'information', 'title', 'pageback'));
    }

    /**
     * Generate Filling Self Evaluation Report
     */
    public function gFillingSelfEvaluation(Request $request)
    {
        ini_set('memory_limit', '256M');

        if ((Cstr::isEmpty($request['period']) || Cstr::isEmpty($request['unit'])) && !empty($request['self'])) {
            $request['period'] = $request['self']['id_audit_periode'];
            $request['unit'] = $request['self']['id_unit'];
        }

        $idPeriod = $request['period'];
        $idUnit = $request['unit'];
        $idJadwalAudit = $request['id_jadwal_audit'];

        // get jadwal audit unit
        $jadwalUnit = JadwalAuditUnit::where([
            'id_jadwal_audit' => $idJadwalAudit,
            'id_unit' => $idUnit
        ])->first();

        // get data master
        $idPengisianPanduan = $jadwalUnit->id_pengisian_panduan ?? null;
        if (empty($idPengisianPanduan)) {
            return view('spmi::pages.reports.404');
        }
        $pengisianPanduan = PengisianPanduan::find($idPengisianPanduan);
        $idPengisianLED = $pengisianPanduan->id_pengisian_panduan ?? null;
        if (empty($idPengisianLED)) {
            return view('spmi::pages.reports.404');
        }

        $fillingIndicator = PengisianIndikator::where([
            'id_unit' => $idUnit,
            'id_audit_periode' => $idPeriod,
            'id_pengisian_panduan' => $idPengisianLED,
            'id_jadwal_audit' => $idJadwalAudit,
            'jenis_indikator' => AkreditasiBuku::SELF_EVALUATION
        ])->first();

        if (empty($fillingIndicator)) {
            return view('spmi::pages.reports.404');
        }

        $fillingIndicatorService = new PengisianIndikatorEvaluasiDiriManagementService;

        $data = $fillingIndicatorService->showReport($fillingIndicator->id_pengisian_panduan, $idPeriod, $idUnit);
        $auditPeriod = AuditPeriode::find($idPeriod);
        $studyProgram = UnitKerja::find($idUnit);

        $records =  PengisianIndikator::getRecordByFilter($idPeriod, $pengisianPanduan->id_lembaga_akreditasi, $fillingIndicator->id_pengisian_panduan, $idUnit, isLED: true, idJadwalAudit: $idJadwalAudit);

        $PengisianPanduan = PengisianPanduan::find($fillingIndicator->id_pengisian_panduan);
        $title = $PengisianPanduan->name;

        $information = [];
        $information['audit_year'] = $auditPeriod->tahun_audit;
        $information['nama_jadwal_audit'] = JadwalAudit::find($idJadwalAudit)?->nama_jadwal_audit;
        $information['study_program'] =  $studyProgram->jenjangPendidikan->kode_jenjang . ' ' . $studyProgram->nama_unit;
        $pageback = $request['pageback'] ?? false;

        return view('spmi::pages.reports.filling-self-report', compact('data', 'information', 'title', 'records', 'pageback'));
    }

    public function gLaporanPTK(Request $request)
    {
        if ((empty($request['period']) || empty($request['unit'])) && !empty($request['self'])) {
            $request['period'] = $request['self']['id_audit_periode'];
            $request['unit'] = $request['self']['id_unit'];
        }

        $idPeriod = $request['period'] ?? null;
        $idUnit = $request['unit'] ?? null;
        $idJadwalAudit = $request['id_jadwal_audit'] ?? null;

        $hasilAkhir = (new HasilAkhirAuditManagementService)->showByPeriodeAudit($idPeriod, $idUnit, $idJadwalAudit);

        $data = [];
        $isUsingKop = $request['using_kop'] ? true : false;
        $data['university'] = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();

        // get data from siakad
        $v1Siakad = new SiakadV1;
        $dataUnivV1 = $v1Siakad->send('select email,alamat,telepon from ref.ms_unit where idunit = ?', [$data['university']->ref_key_siakad])[1][0] ?? [];

        if (!empty($dataUnivV1)) {
            $data['university']->email = $dataUnivV1['email'];
            $data['university']->alamat = $dataUnivV1['alamat'];
            $data['university']->telepon = $dataUnivV1['telepon'];
        }

        if (Error::isError($hasilAkhir)) {
            $prodi = UnitKerja::find($idUnit);
            $data['self'] = new Model();
            $data['self']->nama_prodi = $prodi?->nama_unit;
            $data['self']->periode_audit = AuditPeriode::find($idPeriod)?->tahun_audit;
            $data['self']->nama_jadwal_audit = JadwalAudit::find($idJadwalAudit)?->nama_jadwal_audit;
            $data['self']->nama_jenjang = $prodi?->jenjangPendidikan?->nama_jenjang;

            $jadwalAuditUnit = JadwalAuditUnit::where('id_jadwal_audit', $idJadwalAudit)
                ->where('id_unit', $idUnit)
                ->first();
            $data['self']->panduan_penilaian = PenilaianPanduan::find($jadwalAuditUnit?->id_penilaian_panduan)?->nama_singkat;

            if (!empty($prodi)) {
                $data['self']->nama_fakultas = UnitKerja::find($prodi->id_parent)?->nama_unit;
            }

            if (!empty($prodi->id_pimpinan)) {
                $pegawai = Pegawai::find($prodi->id_pimpinan);
                $biodataKetua = Biodata::find($pegawai->id_biodata);

                $data['self']->kaprodi = ($pegawai->nip ?? '') . ' - ' . $biodataKetua->nama;
            }

            return view('spmi::pages.reports.permintaan-tindakan-koreksi', compact('data', 'isUsingKop'));
        }

        $data['self'] = $hasilAkhir;
        $data['members'] = PenilaianAuditorManagementService::getAuditorMember($data['self']->id_penilaian_audit);
        $data['temuan'] = AuditTemuanManagementService::showAllMatricesByPenilaianPanduan(
            $data['self']->id_penilaian_panduan,
            $idUnit,
            $idPeriod,
            $data['self']->id_penilaian_audit,
            true,
            $idJadwalAudit
        );

        $data['tinjauan_temuan'] = TinjauanTemuanManagementService::showAllMatricesByPenilaianPanduan(
            $data['self']->id_penilaian_panduan,
            $idUnit,
            $idPeriod,
            $data['self']->id_penilaian_audit,
            false,
            $idJadwalAudit
        );

        $akarMasalah = [];
        $rencanaPeningkatanMutu = [];
        foreach ($data['tinjauan_temuan'] as $key => $value) {
            if (!empty($value->akar_masalah) && $value->akar_masalah != '-') {
                $akarMasalah[$value->id] = $value->akar_masalah . ' (' . $value->nomor_penilaian . ')';
            }
            if (!empty($value->rencana_peningkatan_mutu) && $value->rencana_peningkatan_mutu != '-') {
                $rencanaPeningkatanMutu[$value->id] = $value->rencana_peningkatan_mutu . ' (' . $value->nomor_penilaian . ')';
            }
        }

        $data['akar_masalah'] = $akarMasalah;
        $data['rencana_peningkatan_mutu'] = $rencanaPeningkatanMutu;

        return view('spmi::pages.reports.permintaan-tindakan-koreksi', compact('data', 'isUsingKop'));
    }
    public function gLaporanTemuan(Request $request)
    {
        if ((empty($request['period']) || empty($request['unit'])) && !empty($request['self'])) {
            $request['period'] = $request['self']['id_audit_periode'];
            $request['unit'] = $request['self']['id_unit'];
        }

        $idPeriod = $request['period'] ?? null;
        $idUnit = $request['unit'] ?? null;
        $idJadwalAudit = $request['id_jadwal_audit'] ?? null;

        $prodi = UnitKerja::find($idUnit);

        $data = [];
        $data['university'] = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();

        // get data from siakad
        $v1Siakad = new SiakadV1;
        $dataUnivV1 = $v1Siakad->send('select email,alamat,telepon from ref.ms_unit where idunit = ?', [$data['university']->ref_key_siakad])[1][0] ?? [];

        if (!empty($dataUnivV1)) {
            $data['university']->email = $dataUnivV1['email'];
            $data['university']->alamat = $dataUnivV1['alamat'];
            $data['university']->telepon = $dataUnivV1['telepon'];
        }

        $data['self'] = new Model();
        $data['self']->periode_audit = AuditPeriode::find($idPeriod)?->tahun_audit;
        $data['self']->nama_jadwal_audit = JadwalAudit::find($idJadwalAudit)?->nama_jadwal_audit;

        $jadwalAuditUnit = JadwalAuditUnit::where('id_jadwal_audit', $idJadwalAudit)
            ->where('id_unit', $idUnit)
            ->first();
        $data['self']->panduan_penilaian = PenilaianPanduan::find($jadwalAuditUnit?->id_penilaian_panduan)?->nama_singkat;

        if (!empty($prodi)) {
            $data['self']->nama_fakultas = UnitKerja::find($prodi->id_parent)?->nama_unit;
            $data['self']->nama_prodi = $prodi?->jenjangPendidikan?->kode_jenjang . ' - ' . $prodi?->nama_unit;
        }

        $isUsingKop = $request['using_kop'] ? true : false;

        $data['temuan'] = (new DashboardService)->getMostRelevantAuditTemuan($idPeriod, $idUnit, $idJadwalAudit);

        return view('spmi::pages.reports.temuan', compact('data', 'isUsingKop'));
    }
    /**
     * List Report Options
     */
    public function listReport()
    {
        return [
            'plk' => 'Pengisian Laporan Kinerja',
            'pled' => 'Pengisian Laporan Evaluasi Diri',
            'ptk' => 'Laporan PTK',
            // 'ami' => 'Berita Acara Audit(Laporan AMI)',
            // 'skor_ami' => 'Skor Akhir AMI',
            // 'komentar' => 'Daftar Komentar Auditor(Checklist)',
            'temuan' => 'Laporan Temuan',
            // 'praktik_baik' => 'Daftar Praktik Baik',
        ];
    }
}
