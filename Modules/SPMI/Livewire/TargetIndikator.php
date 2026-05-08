<?php

namespace Modules\SPMI\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Modules\Core\Helpers\Error;
use Modules\Core\Livewire\MainComponent;
use Modules\SPMI\Models\TargetIndikator as ModelsTargetIndikator;
use Modules\SPMI\Models\PenilaianMatriks;
use Modules\SPMI\Models\AuditPeriode;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Models\SkorMatriksPredikatPenilaian;
use Modules\SPMI\Services\TargetIndikatorManagementService;
use Modules\SPMI\Services\PenilaianMatriksManagementService;
use Modules\SPMI\Services\TinjauanTemuanManagementService;

class TargetIndikator extends MainComponent
{
    use SpmiViewData;

    public $resourceId = null;
    public $resourceTempId = null;
    public $information = [];
    public $raw = [];

    public $search = '';
    public $filterElement = '';
    public $scoreValues = [];
    public $scorePredikatValues = [];
    public $assessmentMatricesRaw = [];
    public $assessmentMatrices = [];
    public $limit = 15;
    public $isFinalized = false;
    public $isCheckedAll = null;

    // Perizinan
    public $isCanAction = false;
    public $isNotHaveMatriks = false;

    protected $queryString = ['id_unit', 'periode_audit', 'id_jadwal_audit'];

    public $id_unit = null;
    public $periode_audit = null;
    public $id_jadwal_audit = null;

    public function mount()
    {
        parent::mount();

        $this->resourceId = $this->viewData['resourceId'];

        if (!$this->resourceId && !isset($this->id_unit, $this->periode_audit)) {
            abort(404);
        }

        $redirect = $this->loadAchievementTragetProfile();

        if (isset($redirect)) {
            return $redirect;
        }

        // Get data penilaian matriks dan validasi skor indikator
        $temp = $this->service->showAllMatricesByPenilaianPanduan($this->raw['id_audit_periode'], $this->raw['id_unit'], $this->raw['id_penilaian_panduan'], $this->resourceId);
        foreach ($temp as $index => $value) {
            $skor_indikator = json_decode($value->skor_indikator, true);
            if (empty($skor_indikator[0])) {
                continue;
            }
            $temp_nilai = [0, 1, 2, 3, 4];
            foreach ($skor_indikator as $skor) {
                if (in_array($skor['nilai'], $temp_nilai)) {
                    unset($temp_nilai[$skor['nilai']]);
                }
            }

            if (count($temp_nilai) > 0) {
                foreach ($temp_nilai as $nilai) {
                    $skor_indikator[] = [
                        'id' => null,
                        'nilai' => $nilai,
                        'deskripsi' => '',
                        'apakah_nonaktif' => true,
                    ];
                }
            }

            usort($skor_indikator, function ($a, $b) {
                return $b['nilai'] <=> $a['nilai'];
            });

            $value->skor_indikator = json_encode($skor_indikator);
        }
        $this->assessmentMatricesRaw = $temp;
        $totalTargetIndikator = count($temp);
        $this->isNotHaveMatriks = $totalTargetIndikator == 0;
        unset($temp);

        $this->checkForCheckedAll();

        $this->assessmentMatrices = array_slice($this->assessmentMatricesRaw, 0, $this->limit);
        $this->isCanAction = ($this->validatePermission('post') || $this->validatePermission('put')) && $totalTargetIndikator > 0;

        // TODO: TEMPORARY HIDDEN, PERLU DISESUAIKAN MELIHAT MULTI JADWAL AUDIT
        $dataRTM = (new TinjauanTemuanManagementService)->showByAuditPeriode($this->raw['id_audit_periode'], $this->raw['id_unit']);
        if (!empty($dataRTM)) {
            // $detailRTM = route('spmi.tinjauan-temuan.show', [$dataRTM->id]);
            // $this->alert('helper', "Periksa temuan yang ada pada RTM terlebih dahulu sebelum melakukan pengisian target.
            //     <br/>
            //     Silakan klik <a href='{$detailRTM}' target='_blank' rel='noopener noreferrer'><b style='color: #0F6AF5;'>disini</b></a> untuk melihat RTM
            //     periode sebelumnya.");
        }

        $this->scorePredikatValues = SkorMatriksPredikatPenilaian::where('id_penilaian_panduan', $this->raw['id_penilaian_panduan'])
            ->orderBy('nilai', 'desc')
            ->pluck('deskripsi', 'nilai')
            ->toArray();
    }

    public function loadService()
    {
        $this->service = new TargetIndikatorManagementService();
    }

    public function checkForCheckedAll()
    {
        $is_search = ($this->search || $this->filterElement);

        $data = $is_search ? $this->assessmentMatrices : $this->assessmentMatricesRaw;

        $totalIndicator = count(
            array_filter($data, function ($item) {
                return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;
            })
        );

        // cek apabila skor sama semua
        $this->isCheckedAll = null;
        $scoreValues = [];
        foreach ($this->scoreValues as $key => $value) {
            $keyObj = explode('/', $key);

            if (!in_array($keyObj[0], array_column($data, 'id'))) {
                continue;
            }

            $scoreValues[$keyObj[0]][] = $value['nilai'];
        }

        // hitung jumlah di setiap nilai
        $temp = [];
        foreach ($scoreValues as $key => $value) {
            if (!isset($temp[$value[0]])) {
                $temp[$value[0]] = 1;
            } else {
                $temp[$value[0]]++;
            }
        }

        if (count($temp) == 1 && $temp[array_key_first($temp)] == $totalIndicator) {
            $this->isCheckedAll = array_key_first($temp);
        }
    }

    public function finalizeData()
    {
        if (!$this->validatedPermission('put', 'post')) {
            $this->alert('error', 'Anda tidak memiliki akses untuk melakukan finalisasi data.');
            return;
        }

        $totalIndicator = count(
            array_filter($this->assessmentMatricesRaw, function ($item) {
                return $item->kategori_penilaian == PenilaianMatriks::CATEGORY_INDICATOR;
            })
        );

        $checkHasNotFilledScore = ($totalIndicator - count($this->scoreValues)) > 0;

        if ($checkHasNotFilledScore) {
            $this->alert('error', 'Terdapat indikator yang belum diisi skor. Silakan mengisi semua skor terlebih dahulu.');
            return;
        }

        $update = $this->service->update(
            [
                'apakah_terfinalisasi' => true,
                'total_matriks' => $totalIndicator,
            ],
            ($this->resourceId ?? $this->resourceTempId),
        );

        if (Error::isError($update)) {
            $this->alert('error', 'Terjadi kesalahan saat menyimpan data.' . $update->message);
            return;
        }

        $this->isFinalized = true;
        $this->alert('success', 'Berhasil finalisasi data');
    }

    public function unfinalizeData()
    {
        if (!$this->validatedPermission('put', 'post')) {
            $this->alert('error', 'Anda tidak memiliki akses untuk melakukan pembatalan finalisasi data.');
            return;
        }

        // Cek apakah penilaian sudah terfinalisasi
        $isFinalizeAuditorAssessment = $this->service->isFinalizeAuditorAssessment($this->raw['id_audit_periode'], $this->raw['id_unit'], $this->raw['id_penilaian_panduan'], $this->raw['id_jadwal_audit']);
        if ($isFinalizeAuditorAssessment) {
            $this->alert('error', 'Data penilaian auditor sudah terfinalisasi. Anda tidak bisa membatalkan finalisasi data.');
            return;
        };

        $update = $this->service->update(
            [
                'apakah_terfinalisasi' => false
            ],
            ($this->resourceId ?? $this->resourceTempId),
        );

        if (Error::isError($update)) {
            $this->alert('error', 'Terjadi kesalahan saat menyimpan data.' . $update->message);
            return;
        }

        $this->isFinalized = false;
        $this->alert('success', 'Berhasil membatalkan finalisasi data');
    }

    public function render()
    {
        View::share($this->viewData + [
            'isLivewire' => true,
            'permission' => $this->permission,
            'urlInfo' => $this->urlInfo,
            'alert' => $this->alert,
            'headerClass' => 'header_position-static',
        ]);

        return $this->buildView('spmi::pages.target-indikator.create')
            ->layout('core::components.layouts.main-outer');
    }

    public function updatedSearch()
    {
        $this->goSearchData();
    }

    public function updatedFilterElement()
    {
        $this->filterElement = $this->selectValue($this->filterElement);

        $this->goSearchData();
    }

    private function goSearchData()
    {
        if ($this->search || $this->filterElement) {
            $searchData = [];

            if ($this->filterElement) {
                foreach ($this->assessmentMatricesRaw as $key => $penilaianMatriks) {
                    $isContains = false;

                    $nilai = json_decode($penilaianMatriks->skor_indikator, true);

                    if ($penilaianMatriks->kategori_penilaian == PenilaianMatriks::CATEGORY_ELEMENT) {
                        continue;
                    }

                    if (
                        $this->filterElement == 'unchecked' &&
                        $penilaianMatriks->kategori_penilaian === PenilaianMatriks::CATEGORY_INDICATOR && empty($nilai[0])
                    ) {
                        $searchData[] = $penilaianMatriks;
                        continue;
                    }

                    foreach ($this->scoreValues as $kv => $val) {
                        $key = explode('/', $kv);

                        if ($key[0] == $penilaianMatriks->id) {
                            $isContains = true;
                            break;
                        }
                    }

                    $isAdd = ($this->filterElement == 'checked' && $isContains) || ($this->filterElement == 'unchecked' && !$isContains);

                    if ($isAdd) {
                        $searchData[] = $penilaianMatriks;
                    }
                }
            } else {
                $searchData = $this->assessmentMatricesRaw;
            }

            if ($this->search) {
                $searchData = array_filter($searchData, function ($penilaianMatriks) {
                    return stripos($penilaianMatriks->pertanyaan_penilaian, $this->search) !== false;
                });
            }

            $parent = [];
            foreach ($searchData as $penilaianMatriks) {
                $parentId = $penilaianMatriks->id_parent;
                while ($parentId) {
                    foreach ($this->assessmentMatricesRaw as $key => $value) {
                        if ($value->id == $parentId) {
                            $parentId = $value->id_parent;
                            $parent[$value->id] = $value;
                        }
                    }
                }
            }

            $result = [];
            foreach ($parent as $value) {
                $result[$value->id] = $value;
            }
            foreach ($searchData as $value) {
                $result[$value->id] = $value;
            }

            usort($result, function ($a, $b) {
                return $a->info_left <=> $b->info_left;
            });

            $this->assessmentMatrices = $result;
        } else {
            $this->limit = 15;

            $this->assessmentMatrices = array_slice($this->assessmentMatricesRaw, 0, $this->limit);
        }

        $this->checkForCheckedAll();

        $this->dispatch('re-render-scoreall-checkbox', [
            'id' => 'set_all_score_' . ($this->isCheckedAll ? explode('.', $this->isCheckedAll)[0] : '99'),
        ]);
    }

    public function loadMore()
    {
        if (!$this->search && !$this->filterElement) {
            $this->limit += 15;

            $this->assessmentMatrices = array_slice($this->assessmentMatricesRaw, 0, $this->limit);
        }
    }

    public function updateScoreValue($idTargetIndikator, $idPredikatMatriksPenilaian, $nilai, $check = null)
    {
        if (!$this->validatedPermission('put', 'post')) {
            $this->alert('error', 'Anda tidak memiliki akses untuk melakukan perubahan data');
            return;
        }

        $dataCheck = [];
        foreach ($this->scoreValues as $key => $value) {
            $keyObj = explode('/', $key);
            if ($keyObj[0] == $idTargetIndikator) {
                $dataCheck['uncheck_id'] = 'checkbox_' . $idTargetIndikator . '_' . $keyObj[1];
                $dataCheck['uncheck_input_id'] = 'input_' . $idTargetIndikator . '_' . $keyObj[1];
                unset($this->scoreValues[$key]);
            }
        }

        $disableRender = false;
        if ($check || is_null($check)) {
            $data = [
                'id_target_indikator' => $this->resourceId,
                'id_penilaian_matriks' => $idTargetIndikator,
                'id_predikat_matriks_penilaian' => $idPredikatMatriksPenilaian,
                'nilai_default' => $nilai . '.00',
                'nilai' => $nilai . '.00',
            ];

            $this->scoreValues[$idTargetIndikator . '/' . $idPredikatMatriksPenilaian] = $data;

            if ($check) {
                $stored = $this->service->store([$data], $this->raw, ($this->resourceId ?? $this->resourceTempId));
                $this->resourceTempId = $stored->id;

                $dataCheck['check_id'] = 'checkbox_' . $idTargetIndikator . '_' . $idPredikatMatriksPenilaian;
                $dataCheck['check_input_id'] = 'input_' . $idTargetIndikator . '_' . $idPredikatMatriksPenilaian;
            } else {
                $this->service->updateScore(
                    ($this->resourceId ?? $this->resourceTempId),
                    $idTargetIndikator,
                    $nilai,
                );
                $disableRender = true;
            }
        } else {
            $this->service->removeScore(($this->resourceId ?? $this->resourceTempId), $idTargetIndikator);
        }

        $this->checkForCheckedAll();

        $this->dispatch('re-render-scoreall-checkbox', [
            'id' => 'set_all_score_' . ($this->isCheckedAll ? explode('.', $this->isCheckedAll)[0] : '99'),
        ]);

        if (!$disableRender) {
            $this->dispatch('re-render-checkbox', $dataCheck);
        }
    }

    public function resetSearch()
    {
    }

    public function updateAllScoreValue($nilai, $check)
    {
        if (!$this->validatedPermission('put', 'post')) {
            $this->alert('error', 'Anda tidak memiliki akses untuk melakukan perubahan data');
            return;
        }

        $is_search = ($this->search || $this->filterElement);

        $assessmentMatricesData = $is_search ? $this->assessmentMatrices : $this->assessmentMatricesRaw;

        if (!$is_search) {
            $this->scoreValues = [];
        } else {
            foreach ($this->scoreValues as $key => $value) {
                $keyObj = explode('/', $key);

                if (in_array($keyObj[0], array_column($assessmentMatricesData, 'id'))) {
                    unset($this->scoreValues[$key]);
                }
            }
        }

        // FIXME: Perlu optimasi karena terlalu banyak looping
        foreach ($assessmentMatricesData as $assessmentMatrix) {
            if ($assessmentMatrix->kategori_penilaian !== PenilaianMatriks::CATEGORY_INDICATOR) {
                continue;
            }

            $assessmentMatrix = (array) $assessmentMatrix;

            // get score value
            $skorIndikator = json_decode($assessmentMatrix['skor_indikator'], true);
            $scoreValue = null;
            foreach ($skorIndikator as $sc) {
                if (!empty($sc) && $nilai == $sc['nilai']) {
                    $scoreValue = (array) $sc;
                    break;
                }
            }

            if ($scoreValue == null || $scoreValue['apakah_nonaktif']) {
                continue;
            }

            if ($check) {
                $data = [
                    'id_target_indikator' => $this->resourceId,
                    'id_penilaian_matriks' => $assessmentMatrix['id'],
                    'id_predikat_matriks_penilaian' => $scoreValue['id'],
                    'nilai_default' => $nilai . '.00',
                    'nilai' => $nilai . '.00',
                ];

                $stored = $this->service->store([$data], $this->raw, ($this->resourceId ?? $this->resourceTempId));
                $this->resourceTempId = $stored->id;

                $this->scoreValues[$assessmentMatrix['id'] . '/' . $scoreValue['id']] = $data;
            } else {
                $this->service->removeScore(($this->resourceId ?? $this->resourceTempId), $assessmentMatrix['id']);
            }
        }

        $this->checkForCheckedAll();

        $this->dispatch('re-render-scoreall-checkbox', [
            'id' => 'set_all_score_' . ($this->isCheckedAll ? explode('.', $this->isCheckedAll)[0] : '99'),
        ]);
    }

    protected function loadAchievementTragetProfile()
    {
        if ($this->resourceId) {
            $data = $this->service->showInformation((int) $this->resourceId);
            if (Error::isError($data) && $data->code == 404) {
                abort(404);
            }

            // Validasi apakah id_penilaian_panduan sesuai dengan data jadwal audit unit
            $jadwalAuditUnit = DB::table('spmi.jadwal_audit as ja')
                ->join('spmi.jadwal_audit_unit as jau', 'jau.id_jadwal_audit', '=', 'ja.id')
                ->where('ja.id_audit_periode', $data[1]['id_audit_periode'])
                ->where('jau.id_unit', $data[1]['id_unit'])
                ->where('jau.id_penilaian_panduan', $data[1]['id_penilaian_panduan'])
                ->where('ja.waktu_dihapus', null)
                ->select('jau.*')
                ->first();

            if (!$jadwalAuditUnit || $data[1]['id_penilaian_panduan'] != $jadwalAuditUnit->id_penilaian_panduan) {
                abort(404);
            }

            $this->scoreValues = $this->service->showScoreValues((int) $this->resourceId);
        } else {
            $yearData = AuditPeriode::findByYear($this->periode_audit);
            $jadwalAuditQuery = DB::table('spmi.jadwal_audit as ja')
                ->join('spmi.jadwal_audit_unit as jau', 'jau.id_jadwal_audit', '=', 'ja.id')
                ->where('ja.id_audit_periode', $yearData?->id)
                ->where('ja.waktu_dihapus', null)
                ->where('jau.id_unit', $this->id_unit);

            if (!empty($this->id_jadwal_audit)) {
                $jadwalAuditQuery->where('ja.id', $this->id_jadwal_audit);
            }

            $jadwalAuditUnit = $jadwalAuditQuery->select('jau.*')->first();

            if (!$jadwalAuditUnit) {
                abort(404);
            }

            $achievementTarget = ModelsTargetIndikator::findByStudyProgramId($jadwalAuditUnit->id_unit, $yearData?->id, $jadwalAuditUnit->id_penilaian_panduan, !empty($this->id_jadwal_audit) ? (int) $this->id_jadwal_audit : null);

            // Jika sudah dibuat data capaian targetnya, maka redirect ke halaman edit
            if (!!$achievementTarget && ($achievementTarget->id_audit_periode == $yearData?->id)) {
                $this->resourceTempId = $achievementTarget->id;
                return redirect()->route('spmi.target-indikator.edit', [$this->resourceTempId]);
            }

            $data = $this->service->showInformationByStudyProgram((int) $this->id_unit, $yearData?->toArray(), !empty($this->id_jadwal_audit) ? (int) $this->id_jadwal_audit : null);

            if (Error::isError($data) && $data->code == 404) {
                abort(404);
            }
        }

        [$this->information, $this->raw] = $data;
        $this->isFinalized = $this->raw['apakah_terfinalisasi'];
    }

    /**
     * Fungsi untuk ambil value choices.
     */
    protected function selectValue($value)
    {
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }

        return $value ?? null;
    }

    /**
     * Fungsi untuk menampilkan alert.
     *
     * @param string $type
     * @param string $message
     */
    protected function alert($type, $message)
    {
        $this->alert = [
            'type' => $type,
            'message' => $message,
            'isHtml' => true,
        ];
    }

    protected function validatedPermission(...$permission)
    {
        $isValidated = false;

        foreach ($permission as $perm) {
            if ($this->permission[$perm]) {
                $isValidated = true;
            }
        }

        return $isValidated;
    }
}
