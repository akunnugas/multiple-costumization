<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\SiakadV1;
use Modules\Core\Models\UnitKerja;
use Modules\Core\Services\SiakadV1\SettingSimV1ManagementService;
use Modules\Kerjasama\Models\JenisDokumen;
use Modules\Kerjasama\Models\StatusKerjasama;
use Modules\Kerjasama\Services\KerjasamaManagementService;

class LaporanKerjasamaController extends Controller
{
    public function __construct(
        private KerjasamaManagementService $kerjasamaService,
        private SettingSimV1ManagementService $settingSimService
    ) {
    }

    public function index()
    {
        $filters = $this->defineFilter();

        return view('kerjasama::pages.laporan-kerjasama.index', compact('filters'));
    }

    public function show(Request $request)
    {
        $filterFields = $this->defineFilter();
        $rawFilter = $request->only(array_column($filterFields, 'field'));

        $rawFilter = array_map(function ($field) {
            return $field == '*' ? null : $field;
        }, $rawFilter);
        Validator::make($rawFilter, [
            'id_jenis_dokumen' => 'nullable',
            'tanggal_mulai_berlaku' => 'required|date',
            'tanggal_akhir_berlaku' => 'required|date|after_or_equal:tanggal_mulai_berlaku',
        ], [], Cstr::toMap('field', 'label', $filterFields))->validate();
        $filterValues = [];
        foreach ($filterFields as $key => $value) {
            if (!isset($value['showData'])) {
                continue;
            }
            $filterValues[$value['field']] = $value['options'] ?? null;
        }

        $filter = [];
        $filterShow = [];

        foreach ($rawFilter as $key => $value) {

            if (in_array($key, array_keys($filterValues))) {
                $filterShow[] = [
                    'label' => __('kerjasama::laporan_kerjasama.' . $key),
                    'field' => $key,
                    'text' => $filterValues[$key][$value] ?? null
                ];
            }

            if (empty($value) || $value == '*' || $key == 'menggunakan_kop') {
                continue;
            }

            $filter[$key] = [
                'field' => $key,
                'value' => $value
            ];

        }

        $header = [
            ['field' => 'id_unit_kerja', 'options' => [], 'component' => true],
            ['field' => 'id_mitra', 'component' => true, 'options' => []],
            ['field' => 'judul_kerjasama'],
            ['field' => 'tanggal_mulai_berlaku', 'label' => 'Durasi Kerjasama', 'component' => true],
            ['field' => 'id_sumber_dana'],
            ['field' => 'anggaran', 'component' => true, 'label' => 'Anggaran (Rp)'],
            // ['field' => 'realisasi', 'component' => true, 'label' => 'Realisasi (Rp)'],
            ['field' => 'id_status_kerjasama', 'options' => []],
        ];

        foreach ($header as $key => $value) {
            if (!empty($value['label'])) {
                continue;
            }

            $header[$key]['label'] = __('kerjasama::data_kerjasama.' . $value['field']);
        }

        $data = $this->kerjasamaService->report(filter: $filter);

        // prepare get data univ
        $dataUniv = UnitKerja::where('jenis_unit', UnitKerja::UNIVERSITY)->first();
        $v1Siakad = new SiakadV1;

        // mendapatkan data kop dari v1
        $dataKop = $this->settingSimService->get('report_header', [
            'idjenissetting' => 'UNIV'
        ]);

        if (Error::isError($dataKop)) {
            return $data->redirectBack();
        }

        // get data univ
        $datav1 = $v1Siakad->send('select email,alamat,telepon from ref.ms_unit where idunit = ?', [$dataUniv->ref_key_siakad])[1][0] ?? [];
        $datav1['nama'] = $dataUniv->nama_unit;

        // load konfigurasi
        $isUsingKop = $request->get('menggunakan_kop', 'off') == 'on';

        return view(
            'kerjasama::pages.laporan-kerjasama.show',
            compact('data', 'header', 'filter', 'filterShow', 'datav1', 'isUsingKop', 'dataKop')
        );
    }

    private function defineFilter(): array
    {
        $unitKerjaOptions = $this->kerjasamaService->getUnitKerjaOptions();
        $filters = [
            [
                'field' => 'tanggal_mulai_berlaku',
                'type' => 'date',
                'defaultTypeDate' => 'd F Y',
                'required' => true,
                'column' => 6,
            ],
            [
                'field' => 'tanggal_akhir_berlaku',
                'type' => 'date',
                'required' => true,
                'defaultTypeDate' => 'd F Y',
                'column' => 6,
            ],
            [
                'field' => 'id_jenis_dokumen',
                'options' => ['*' => 'Semua Jenis Dokumen Kerjasama'] + JenisDokumen::options(),
                'selected' => '*',
                'required' => true,
                'variant' => 'search',
                'withLabel' => false,
                'showData' => true
            ],
            [
                'field' => 'id_unit_kerja',
                'options' => ['*' => 'Semua Unit Kerja'] + $unitKerjaOptions,
                'selected' => '*',
                'variant' => 'search',
                'withLabel' => false,
                'showData' => true
            ],
            [
                'field' => 'id_status_kerjasama',
                'options' => ['*' => 'Semua Status Kerjasama'] + StatusKerjasama::options(),
                'selected' => '*',
                'variant' => 'search',
                'withLabel' => false,
                'showData' => true
            ],
            [
                'field' => 'menggunakan_kop',
                'choiceLabel' => 'Menggunakan KOP Laporan',
                'control' => 'checkbox'
            ],
        ];

        foreach ($filters as $key => $value) {
            $filters[$key]['label'] = __("kerjasama::laporan_kerjasama." . $value['field']);
        }

        return $filters;
    }
}
