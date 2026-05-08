@props([
    'data' => [],
    'title' => null,
    'pageback' => false,
])
@php
    $client = request()->client;

    $first = $data->first();

    $service = new Modules\SPMI\Services\PengisianIndikatorManagementService();

    $raw = [];
    $info = [
        'id_audit_periode' => $information['audit']->id,
        'id_unit' => $information['study_program']->id,
        'id_lembaga_akreditasi' => $information['study_program']->id_lembaga_akreditasi,
        'id_pengisian_panduan' => $first->id_pengisian_panduan,
        'id_jenjang_pendidikan' => $information['study_program']->id_jenjang_pendidikan,
    ];

    $idsDataPengisian = [];
    if ($information['apakah_data_default']) {
        foreach ($data as $indikator) {
            if (!$indikator['apakah_parent']) {
                if ($indikator['apakah_data_default']) {
                    // init class
                    $classPath = 'Modules\SPMI\Services\Pengisian\\' . str_replace('.', '', $information['kode_panduan']) . 'ManagementService';
                    if (!class_exists($classPath)) {
                        throw new \Exception("Class {$classPath} not found");
                    }

                    // get function
                    $class = new $classPath($information['audit']->tahun_audit);
                    $function = 'get' . str_replace('.', '', $indikator['nomor_indikator']);
                    $function = str_replace('-', '_', $function);

                    // get data
                    [
                        $table,
                        $table_row,
                        $table_footer,
                        $table_data,
                        $isHasNumber,
                        $isHasAction,
                        $updatedIndicator,
                    ] = $class->$function(
                        array_merge($info, ['id_indikator_laporan_kinerja' => $indikator['id']]),
                        $information['id_pengisian_indikator'],
                    );

                    [$table_column, $table_input, $table_disabled] = $service->convertTable(
                        $table,
                        $isHasNumber,
                        $isHasAction,
                    );

                    $raw[$indikator['id']] = [
                        'table_column' => $table_column,
                        'table_input' => $table_input,
                        'table_row' => $table_row,
                        'table_footer' => $table_footer,
                        'table_data' => $table_data,
                        'table_disabled' => $table_disabled,
                    ];
                } else {
                    $idsDataPengisian[] = $indikator['id'];
                }
            }
        }
    } else {
        $dataPengisian = Modules\SPMI\Models\DataPengisianLK::where('id_pengisian_indikator', $information['id_pengisian_indikator'])
            ->get()
            ->groupBy('id_indikator_laporan_kinerja');
    }
    if (!empty($idsDataPengisian)) {
        $dataPengisian = Modules\SPMI\Models\DataPengisianLK::where('id_pengisian_indikator', $information['id_pengisian_indikator'])
            ->whereIn('id_indikator_laporan_kinerja', $idsDataPengisian)
            ->get()
            ->groupBy('id_indikator_laporan_kinerja');
    }
@endphp
<x-core::layouts.reports.show :title="$title" :pageback="$pageback">
    @push('head')
        @vite('Modules/Core/Resources/assets/sass/reports/report.scss')
        <style>
            .subtitle {
                font-weight: bold;
                margin-bottom: 0;
            }

            @print {
                @page {
                    size: landscape;
                }
            }
        </style>
    @endpush
    <div class="content">
        <div class="page page-center page-information lk-page">
            <img height="220" width="210"
                src="{{ session('token.logo_univ') ?? Page::quantumAsset('images/logo-kampus.png') }}">
            <h3>LAPORAN PENGISIAN KINERJA</h3>
            <div class="header-information">
                <h3>AMI</h3>
                <h4>{{ $information['nama_jadwal_audit'] ?? '' }}</h4>
                <h4>{{ $information['study_program_name'] }}</h4>
            </div>
            <div class="body-information">
                <h4>UNIVERSITAS</h4>
                <h4>{{ $client['nama_klien'] ?? config('app.name') }}</h4>
            </div>
            <div class="footer-information">
                <h4>TAHUN {{ $information['audit']->tahun_audit }}</h4>
            </div>
        </div>
        <div class="page-breaker-stop">&nbsp;</div>
        <div class="page">
            <p class="title-page">Indikator Laporan Kinerja</p>
        </div>
        <div class="page">
            @foreach ($data as $indicator)
                @php
                    $dt = $raw[$indicator['id']] ?? [];
                @endphp
                <div style="padding-left: {{ $indicator['info_level'] * 10 }}px;">
                    <br>
                    <p class="subtitle">{{ $indicator['nama_indikator_laporan_kinerja'] }}</p>
                    @if ($indicator['apakah_data_default'])
                        @if (!$indicator['apakah_parent'])
                            <div class="parent">
                                <table class="table table-content">
                                    <thead>
                                        @php
                                            $dCount = 0;
                                        @endphp
                                        <tr>
                                            @foreach ($dt['table_column'] as $key => $value)
                                                @if ($value == '_no_')
                                                    <th rowspan="3" class="horizontal">No.</th>
                                                @elseif ($value == '_action_')
                                                    @php
                                                        $dCount--;
                                                    @endphp
                                                @elseif (is_array($value))
                                                    @php
                                                        $colspan = 0;
                                                        foreach ($value as $sub_value) {
                                                            if (is_array($sub_value)) {
                                                                $colspan += count($sub_value);
                                                            } else {
                                                                $colspan++;
                                                            }
                                                        }
                                                    @endphp
                                                    <th rowspan="1" colspan="{{ $colspan }}" class="horizontal">
                                                        {!! $key !!}</th>
                                                @else
                                                    <th rowspan="3" class="horizontal">{!! $value !!}</th>
                                                @endif
                                                @php
                                                    if (is_array($value)) {
                                                        foreach ($value as $sub_value) {
                                                            if (is_array($sub_value)) {
                                                                $dCount += count($sub_value);
                                                            } else {
                                                                $dCount++;
                                                            }
                                                        }
                                                    } else {
                                                        $dCount++;
                                                    }
                                                @endphp
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach ($dt['table_column'] as $key => $value)
                                                @if (is_array($value))
                                                    @foreach ($value as $sub_key => $sub_value)
                                                        @if (is_array($sub_value))
                                                            <th rowspan="1" colspan="{{ count($sub_value) }}"
                                                                class="horizontal">{!! $sub_key !!}</th>
                                                        @else
                                                            <th rowspan="2" class="horizontal">
                                                                {!! $sub_value !!}</th>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach ($dt['table_column'] as $key => $value)
                                                @if (is_array($value))
                                                    @foreach ($value as $sub_value)
                                                        @if (is_array($sub_value))
                                                            @foreach ($sub_value as $sub_sub_value)
                                                                <th style="min-width: 100px;">{!! $sub_sub_value !!}
                                                                </th>
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @for ($i = 1; $i <= $dCount; $i++)
                                                <th>{{ $i }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($indicator['jenis_form'] == Modules\SPMI\Models\IndikatorLaporanKinerja::FORM_ROW)
                                            @include('spmi::pages.pengisian-indikator.partials.table_row', [
                                                'data' => $dt,
                                                'isAction' => false,
                                            ])
                                        @elseif ($indicator['jenis_form'] == Modules\SPMI\Models\IndikatorLaporanKinerja::FORM_COLUMN)
                                            @include(
                                                'spmi::pages.pengisian-indikator.partials.table_column',
                                                [
                                                    'data' => $dt,
                                                ]
                                            )
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <p class="information">
                                <small>{!! $indicator['informasi'] !!}</small>
                            </p>
                            <br>
                        @endif
                    @else
                        @php
                            $pengisianLK = $dataPengisian[$indicator['id']] ?? collect();
                            if ($pengisianLK->isNotEmpty()) {
                                $pengisianLK = $pengisianLK->first();
                            } else {
                                $pengisianLK = null;
                            }
                        @endphp
                        @if ($pengisianLK)
                            <br>
                            {!! $pengisianLK->teks_pengisian !!}
                            <br>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-core::layouts.reports.show>
