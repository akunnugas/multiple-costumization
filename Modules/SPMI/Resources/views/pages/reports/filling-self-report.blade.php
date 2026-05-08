@props([
    'data' => [],
    'title' => null,
    'pageback' => false,
])
@php
    $client = request()->client;

    $indicators = $data;
@endphp
<x-core::layouts.reports.show :title="$title" :pageback="$pageback">
    @push('head')
        <link href="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.snow.css') }}" rel="stylesheet">
        @vite('Modules/Core/Resources/assets/sass/reports/report.scss')
        <style>
            .subtitle {
                font-weight: bold;
                margin-bottom: 0;
            }
        </style>
    @endpush
    <div class="content">
        <div class="page page-center page-information">
            <img height="220" width="210"
                src="{{ session('token.logo_univ') ?? Page::quantumAsset('images/logo-kampus.png') }}">
            <h3>LAPORAN EVALUASI DIRI</h3>
            <div class="header-information">
                <h3>AMI</h3>
                <h4>{{ $information['nama_jadwal_audit'] ?? '' }}</h4>
                <h4>{{ $information['study_program'] }}</h4>
            </div>
            <div class="body-information">
                <h4>UNIVERSITAS</h4>
                <h4>{{ $client['nama_klien'] ?? config('app.name') }}</h4>
            </div>
            <div class="footer-information">
                <h4>TAHUN {{ $information['audit_year'] }}</h4>
            </div>
        </div>
        <div class="page-breaker-stop">&nbsp;</div>
        <div class="page">
            <p class="title-page">Indikator Evaluasi Diri</p>
        </div>
        <!-- indicator table -->
        <div class="page">
            @foreach ($indicators as $item)
                @php
                    $padding = $item->info_level * 17;
                @endphp
                <div style="padding-left: {{ $padding }}px;">
                    <br>
                    <p class="subtitle">{{ $item->nama_indikator_evaluasi_diri }}</p>
                    @php
                        $value = null;
                        if (!empty($records[$item->id])) {
                            $value = $records[$item->id];
                        }
                    @endphp
                    @if ($value)
                        <br>
                        {!! $value !!}
                        <br>
                    @endif

                    <p class="information"> <small>{!! $item->deskripsi !!} </small></p>
                    <br>
                </div>
            @endforeach
        </div>
    </div>
</x-core::layouts.reports.show>
