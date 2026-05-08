@php

    // fetch data untuk lazy load
    $biodataIds = [];
    $idIndex = 0;
    foreach ($data['table_row'] as $label => $col) {
        $idIndex++;
        $currentData = $data['table_data'][$idIndex] ?? [];
        if (!empty($currentData)) {
            $val = $currentData[0];
            $sIndex = 0;
            foreach ($data['table_input'] as $sValue) {
                $inc = $data['table_input'][0] == '_no_' ? 2 : 1;
                $v = $val[$sIndex + $inc] ?? '';

                if (is_array($sValue)) {
                    $inputType = explode(':', $sValue[0])[0];
                    if ($inputType == 'select_lazy') {
                        $apiUrl = $sValue[1] ?? '';
                        // Only collect IDs if this is a dosen/biodata lazy-load
                        if (str_contains($apiUrl, 'search-dosen') || str_contains($apiUrl, 'dosen-option')) {
                            if (!empty($v) && is_numeric($v)) {
                                $biodataIds[] = $v;
                            }
                        }
                    }
                }
                $sIndex++;
            }
        }

        if (isset($col['childs'])) {
            foreach ($col['childs'] as $key => $label) {
                $idIndex++;
                $currentData = $data['table_data'][$idIndex] ?? [];
                if (!empty($currentData)) {
                    $val = $currentData[0];
                    $sIndex = 0;
                    foreach ($data['table_input'] as $sValue) {
                        $inc = $data['table_input'][0] == '_no_' ? 2 : 1;
                        $v = $val[$sIndex + $inc] ?? '';

                        if (is_array($sValue)) {
                            $inputType = explode(':', $sValue[0])[0];
                            if ($inputType == 'select_lazy') {
                                $apiUrl = $sValue[1] ?? '';
                                if (str_contains($apiUrl, 'search-dosen') || str_contains($apiUrl, 'dosen-option')) {
                                    if (!empty($v) && is_numeric($v)) {
                                        $biodataIds[] = $v;
                                    }
                                }
                            }
                        }
                        $sIndex++;
                    }
                }
            }
        }
    }
    $biodataNames = !empty($biodataIds) ? \Modules\Core\Models\Biodata::getPengisianDataDosenNamesByIds($biodataIds) : [];

    $mapData = [];
    $mapDataChilds = [];
    $d_index = 0;
    foreach ($data['table_row'] as $label => $col) {
        $i_key = $data['table_input'][0] == '_no_' ? 2 : 1;
        $dNew[0][$i_key] = $label;
        foreach ($data['table_input'] as $s_value) {
            if (is_array($s_value)) {
                $input_type = explode(':', $s_value[0])[0];
            } else {
                $input_type = $s_value;
            }
            $i_key++;
            $dNew[0][$i_key] = '';
        }
        $mapData[$d_index] = $dNew;

        if (isset($col['childs'])) {
            foreach ($col['childs'] as $key => $label) {
                $i_key = $data['table_input'][0] == '_no_' ? 2 : 1;
                $dNew[0][$i_key] = $label;
                foreach ($data['table_input'] as $s_value) {
                    if (is_array($s_value)) {
                        $input_type = explode(':', $s_value[0])[0];
                    } else {
                        $input_type = $s_value;
                    }
                    $i_key++;
                    $dNew[0][$i_key] = '';
                }
                $mapDataChilds[$d_index][] = $dNew;
            }
        }

        $d_index++;
    }

    $mapTemp = array_values($data['table_row']);

    $rowIndex = 0;

    $idIndex = 0;
@endphp

@foreach ($mapData as $d_key => $arr)
    @php
        $val = $arr[0];

        $rowIndex++;

        $idIndex++;

        $currentData = $data['table_data'][$idIndex] ?? [];

        if (!empty($currentData)) {
            $val = $currentData[0] + $val;
        }

        ksort($val);

        $listData = [];

        if (($mapTemp[$d_key]['readonly'] ?? false) == false) {
            $listData[] = $val;
        }
    @endphp

    @include('spmi::pages.pengisian-indikator.partials.column_input', [
        'tableInput' => $data['table_input'],
        'tableDisabled' => $data['table_disabled'],
        'val' => $val,
        'index' => $rowIndex,
        'dataIndex' => $idIndex,
        'dataLabels' => $mapTemp[$d_key]['data_labels'] ?? [],
        'dataUnits' => $mapTemp[$d_key]['data_units'] ?? [],
        'isHeader' => ($mapTemp[$d_key]['readonly'] ?? false),
        'isRawHtml' => ($mapTemp[$d_key]['is_raw_html'] ?? false),
        'biodataNames' => $biodataNames,
    ])

    @if (isset($mapDataChilds[$d_key]))
        @foreach ($mapDataChilds[$d_key] as $d_key_child => $arr_child)
            @php
                $val = $arr_child[0];

                $vIndex = null;
                if (!isset($mapTemp[$d_key]['childs'][$d_key_child])) {
                    $rowIndex++;
                    $vIndex = $rowIndex;
                }

                $idIndex++;

                $currentData = $data['table_data'][$idIndex] ?? [];

                if (!empty($currentData)) {
                    $val = $currentData[0] + $val;
                }

                ksort($val);

                $listData[] = $val;
            @endphp

            @include('spmi::pages.pengisian-indikator.partials.column_input', [
                'tableInput' => $data['table_input'],
                'tableDisabled' => $data['table_disabled'],
                'val' => $val,
                'index' => $vIndex,
                'dataIndex' => $idIndex,
                'dataLabels' => [],
                'dataUnits' => [],
                'isRawHtml' => ($mapTemp[$d_key]['is_raw_html'] ?? false),
                'biodataNames' => $biodataNames,
            ])
        @endforeach
    @endif

    {{-- footer --}}
    @if (isset($mapTemp[$d_key]['_footer_']))
        @include('spmi::pages.pengisian-indikator.partials.row_footer', [
            'tableInput' => $data['table_input'],
            'aData' => $listData,
            'col' => $mapTemp[$d_key]['_footer_'],
            'isAction' => false,
        ])
    @endif
@endforeach

{{-- footer --}}
@if (isset($data['table_footer']))
    @php
        $listData = [];
        if (!empty($data['table_row'])) {
            foreach ($data['table_data'] ?? [] as $val) {
                foreach ($val as $v) {
                    $listData[] = $v;
                }
            }
        } else {
            $listData = $data['table_data'];
        }
    @endphp
    @foreach ($data['table_footer'] as $col)
        @include('spmi::pages.pengisian-indikator.partials.row_footer', [
            'tableInput' => $data['table_input'],
            'aData' => $listData,
            'col' => $col,
            'isAction' => false,
        ])
    @endforeach
@endif

<x-core::controls.form :name="'form_type'" :type="'hidden'" :value="Modules\SPMI\Models\IndikatorLaporanKinerja::FORM_COLUMN" />
<x-core::controls.form :name="'column_length'" :type="'hidden'" :value="$idIndex - 1" />
