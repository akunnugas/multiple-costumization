<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Models\UnitKerja;
use Modules\SPMI\Export\ExportPenilaianSheet;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\PenilaianPanduan;
use Modules\SPMI\Services\PenilaianMatriksIKTManagementService;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetException;

class UploadPenilaianIktModal extends Component
{
    use WithFileUploads;

    public $uploadId = "upload-modal";
    public $showModal = false;

    public $panduan = [];
    public $panduanId;
    public $unitKerja = [];
    public $unitKerjaIds = [];
    public $periods = [];
    public $selectedPeriods = [];
    public $fileBase64;

    protected $listeners = [
        'showUploadIktModal' => 'show',
        'unitKerjaIdsChanged' => 'setUnitKerja',
        'periodsChanged' => 'setPeriods',
        'fileBase64Changed' => 'setFileBase64',
    ];

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
        $this->unitKerjaIds = $detail;
    }

    public function setPeriods($detail)
    {
        $this->selectedPeriods = $detail;
    }

    public function setFileBase64($detail)
    {
        $this->fileBase64 = $detail;
    }

    public function mount()
    {
        $this->panduan = ['' => 'Pilih Panduan Penilaian'] + PenilaianPanduan::options();
        $this->unitKerja = UnitKerja::optionByType(type: [UnitKerja::STUDY_PROGRAM, UnitKerja::UNIT_NON_PRODI], isOnlyActive: true);
        $this->unitKerja = ['' => 'Pilih Unit Kerja'] + $this->unitKerja;
        $this->periods = AuditPeriode::select('id', 'tahun_audit')
            ->orderBy('tahun_audit', 'desc')
            ->pluck('tahun_audit', 'id')
            ->toArray();
        $this->periods = ['' => 'Pilih Periode'] + $this->periods;
    }

    public function render()
    {
        return view('spmi::components.detail.upload-penilaian-ikt-modal');
    }

    public function clearForm()
    {
        $this->panduanId = null;
        $this->unitKerjaIds = [];
        $this->selectedPeriods = [];
        $this->uploadId = "upload-modal" . rand();
        $this->fileBase64 = null;
        $this->showModal = false;
    }

    public function submit()
    {
        if (
            empty($this->panduanId) ||
            empty($this->fileBase64)
        ) {
            return;
        }

        $tmpFile = null;

        try {
            // Decode base64
            $base64 = preg_replace('/^data:.*;base64,/', '', $this->fileBase64);
            $fileContent = base64_decode($base64, true);

            if ($fileContent === false) {
                throw new \RuntimeException('Gagal decode base64');
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

            ini_set('memory_limit', '256M');

            $sheets = Excel::toArray(
                [],
                $uploadedFile,
                null,
                MaatwebsiteExcel::XLSX
            );

            $panduan = PenilaianPanduan::findOrFail($this->panduanId);
            $service = new PenilaianMatriksIKTManagementService();

            if ($panduan->apakah_data_default) {
                [$success, $message] = $service->importFromExcelDefault(
                    data: $sheets,
                    idPenilaianPanduan: $this->panduanId,
                    unitKerjaIds: $this->unitKerjaIds,
                    periodIds: $this->selectedPeriods,
                    uploadedFilePath: $tmpFile
                );
            } else {
                [$success, $message] = $service->importFromExcelNonDefault(
                    data: $sheets,
                    idPenilaianPanduan: $this->panduanId,
                    unitKerjaIds: $this->unitKerjaIds,
                    periodIds: $this->selectedPeriods,
                    uploadedFilePath: $tmpFile
                );
            }

            if ($success) {
                session()->flash('success', $message ?? 'Import berhasil!');
            } else {
                session()->flash('error', $message ?? 'Import gagal!');
            }
        } catch (SpreadsheetException $e) {
            session()->flash(
                'error',
                '<b>Format file tidak valid</b>.<br/>Gunakan template <b>Excel Workbook (.xlsx)</b> sesuai panduan pada <a href="https://knowledge.sevima.com/panduan-import-data-lk-dan-ed-tambahan-serta-matriks-penilaian-ikt" target="_blank" style="color: blue; text-decoration: underline;">Knowledge Base</a>.'
            );
        } catch (\Throwable $e) {
            session()->flash(
                'error',
                'Terjadi kesalahan saat memproses file import.'
            );
        } finally {
            // Pastikan file temp selalu dihapus
            if ($tmpFile && file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }

        // redirect
        $this->redirect(
            route('spmi.penilaian-matriks-ikt.index', [
                'filter' => [
                    'id_penilaian_panduan' => $this->panduanId
                ]
            ])
        );
    }

    public function downloadTemplate()
    {
        $panduan = PenilaianPanduan::find($this->panduanId);
        $fileName = 'Template Penilaian Custom.xlsx';
        if ($panduan && $panduan->apakah_data_default) {
            $fileName = 'Template Penilaian Default Sistem.xlsx';
        }
        return Excel::download(new ExportPenilaianSheet($this->panduanId, $panduan->apakah_data_default), $fileName);
    }
}
