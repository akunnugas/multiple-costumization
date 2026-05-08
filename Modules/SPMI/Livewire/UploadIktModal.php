<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PengisianPanduan;
use Modules\SPMI\Services\ImportIndikatorService;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetException;

class UploadIktModal extends Component
{
    use WithFileUploads;

    public $uploadId = "upload-pengisian-modal";
    public $showModal = false;

    public $jenisIndikator = 'lk';
    public $fileBase64;

    public $panduan = [];
    public $selectedPanduan = null;

    public $unitKerja = [];
    public $selectedUnitKerja = [];

    public $periods = [];
    public $selectedPeriods = [];

    protected $listeners = [
        'showUploadPengisianIktModal' => 'show',
        'unitKerjaChanged' => 'setUnitKerja',
        'periodChanged' => 'setPeriod',
        'fileChanged' => 'setFileBase64',
    ];

    public function mount()
    {
        if ($this->jenisIndikator == 'lk') {
            $this->panduan = PengisianPanduan::getListIndicatorPerformanceReport();
        } else {
            $this->panduan = PengisianPanduan::getListSelfEvaluation();
        }
        $this->panduan = ['' => 'Pilih Panduan Pengisian'] + $this->panduan;

        $this->unitKerja = UnitKerja::optionByType(type: [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);
        $this->unitKerja = ['' => 'Pilih Unit Kerja'] + $this->unitKerja;
        $this->periods = AuditPeriode::select('id', 'tahun_audit')
            ->orderBy('tahun_audit', 'desc')
            ->pluck('tahun_audit', 'id')
            ->toArray();
        $this->periods = ['' => 'Pilih Periode'] + $this->periods;
    }

    public function show()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function setUnitKerja($detail)
    {
        $this->selectedUnitKerja = $detail;
    }

    public function setPeriod($detail)
    {
        $this->selectedPeriods = $detail;
    }

    public function setFileBase64($detail)
    {
        $this->fileBase64 = $detail;
    }

    public function render()
    {
        return view('spmi::components.detail.upload-ikt-modal');
    }

    public function clearForm()
    {
        $this->selectedPanduan = null;
        $this->selectedUnitKerja = [];
        $this->selectedPeriods = [];
        $this->uploadId = "upload-modal" . rand();
        $this->fileBase64 = null;
        $this->showModal = false;
    }

    public function submit()
    {
        if (
            empty($this->selectedPanduan) ||
            empty($this->fileBase64)
        ) {
            return;
        }

        $route = $this->jenisIndikator == 'lk'
            ? 'spmi.indikator-lk-tambahan.index'
            : 'spmi.indikator-led-tambahan.index';

        $tmpFile = null;

        try {
            // Decode base64
            $base64 = preg_replace('/^data:.*;base64,/', '', $this->fileBase64);
            $fileContent = base64_decode($base64, true);

            if ($fileContent === false) {
                throw new \RuntimeException('Gagal decode file base64');
            }

            // Simpan ke file sementara
            $tmpFile = tempnam(sys_get_temp_dir(), 'excel_');
            file_put_contents($tmpFile, $fileContent);

            // Buat UploadedFile instance
            $uploadedFile = new UploadedFile(
                $tmpFile,
                'import.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                null,
                true
            );

            $sheets = Excel::toArray(
                [],
                $uploadedFile,
                null,
                MaatwebsiteExcel::XLSX
            );

            // Proses import
            $service = new ImportIndikatorService(
                sheets: $sheets,
                jenisIndikator: $this->jenisIndikator,
                panduanId: $this->selectedPanduan,
                unitIds: $this->selectedUnitKerja,
                periodeIds: $this->selectedPeriods
            );

            [$success, $message] = $service::import();

            if ($success) {
                return redirect()
                    ->route($route, [
                        'filter' => [
                            'id_pengisian_panduan' => $this->selectedPanduan
                        ]
                    ])
                    ->with('success', $message ?? 'Import berhasil!');
            }

            return redirect()
                ->route($route)
                ->with('error', $message ?? 'Import gagal!');
        } catch (SpreadsheetException $e) {
            return redirect()
                ->route($route)
                ->with(
                    'error',
                    '<b>Format file tidak valid</b>.<br/>Gunakan template <b>Excel Workbook (.xlsx)</b> sesuai panduan pada <a href="https://knowledge.sevima.com/panduan-import-data-lk-dan-ed-tambahan-serta-matriks-penilaian-ikt" target="_blank" style="color: blue; text-decoration: underline;">Knowledge Base</a>.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->route($route)
                ->with(
                    'error',
                    'Terjadi kesalahan saat memproses file import.'
                );
        } finally {
            if ($tmpFile && file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}
