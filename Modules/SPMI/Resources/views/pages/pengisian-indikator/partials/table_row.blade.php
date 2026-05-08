@php
    $columnLength = count($data['table_input']);
@endphp
@if (empty($data['table_row']))
    @php
        $dataNew = [];
        $i_key = 0;
        foreach ($data['table_input'] as $s_value) {
            if (is_array($s_value)) {
                $input_type = explode(':', $s_value[0])[0];

                if ($input_type == 'select') {
                    $input_options = [];
                    foreach ($s_value[1] as $opt) {
                        $input_options[$opt] = $opt;
                    }
                } else {
                    $input_value = $s_value[1];
                }
            } else {
                $input_type = $s_value;
            }
            $i_key++;
            $dataNew[0][$i_key] = '';
        }

        $isEmptyData = true;
        if (!empty($data['table_data'])) {
            $isEmptyData = false;
        }
    @endphp

    {{-- edit data --}}
    @include('spmi::pages.pengisian-indikator.partials.row_input', [
        'isAction' => $isAction,
        'tableInput' => $data['table_input'],
        'data' => $data['table_data'],
        'isCreate' => false,
    ])

    {{-- create data --}}
    @if (!request()->has('id_record') && $isAction)
        @include('spmi::pages.pengisian-indikator.partials.row_input', [
            'isAction' => $isAction,
            'tableInput' => $data['table_input'],
            'data' => $dataNew,
            'isCreate' => true,
        ])
    @endif

    {{-- empty data --}}
    @if ($isEmptyData && !$isAction)
        <tr>
            <td colspan="100%" style="text-align: center;">
                Data tidak tersedia
            </td>
        </tr>
    @endif
@else
    @php
        $dataNew = [];
        foreach ($data['table_row'] as $label => $col) {
            $i_key = 0;
            foreach ($data['table_input'] as $s_value) {
                if (is_array($s_value)) {
                    $input_type = explode(':', $s_value[0])[0];
                } else {
                    $input_type = $s_value;
                }
                $i_key++;
                $dataNew[$col['data_index']][0][$i_key] = '';
            }
        }
    @endphp
    @foreach ($data['table_row'] as $label => $col)
        @php
            $isEmptyData = true;
            if (!empty($data['table_data'][$col['data_index']] ?? [])) {
                $isEmptyData = false;
            }
        @endphp

        <tr>
            <td colspan="100%">
                @if (isset($col['is_raw_html']) && $col['is_raw_html'] == true)
                    {!! $label !!}
                @else
                    <b>{{$label}}</b>
                @endif
            </td>
        </tr>

        {{-- edit data --}}
        @include('spmi::pages.pengisian-indikator.partials.row_input', [
            'isAction' => $isAction,
            'col' => $col,
            'tableInput' => $data['table_input'],
            'data' => $data['table_data'][$col['data_index']] ?? [],
            'isCreate' => false,
        ])

        {{-- create data --}}
        @if (!request()->has('id_record') && $isAction)
            @include('spmi::pages.pengisian-indikator.partials.row_input', [
                'isAction' => $isAction,
                'col' => $col,
                'tableInput' => $data['table_input'],
                'data' => $dataNew[$col['data_index']] ?? [],
                'isCreate' => true,
            ])
        @endif

        {{-- empty data --}}
        @if ($isEmptyData && !$isAction)
            <tr>
                <td colspan="100%" style="text-align: center;">
                    Data tidak tersedia
                </td>
            </tr>
        @endif

        {{-- footer --}}
        @if (isset($col['_footer_']))
            @include('spmi::pages.pengisian-indikator.partials.row_footer', [
                'tableInput' => $data['table_input'],
                'aData' => $data['table_data'][$col['data_index']] ?? [],
                'col' => $col['_footer_'],
                'isAction' => $isAction,
            ])
        @endif
    @endforeach
@endif

{{-- footer --}}
@if (!empty($data['table_footer']))
    @php
        $listData = [];
        if (!empty($data['table_row'])) {
            foreach ($data['table_data'] as $val) {
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
            'isAction' => $isAction,
        ])
    @endforeach
@endif

<x-core::controls.form :name="'form_type'" :type="'hidden'" :value="Modules\SPMI\Models\IndikatorLaporanKinerja::FORM_ROW" />
@if (empty($data['table_row']))
    <x-core::controls.form :name="'row_type'" :type="'hidden'" :value="'0'" />
@else
    <x-core::controls.form :name="'row_type'" :type="'hidden'" :value="'1'" />
@endif
<x-core::controls.form :name="'column_length'" :type="'hidden'" :value="$columnLength" />
