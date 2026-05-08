@php
    use Carbon\Carbon;
@endphp
<x-core::quantum-3.layouts.report.show title="Laporan Kerjasama" :pageBack="route('kerjasama.laporan-kerjasama.index')">
    @push('head')
        @vite('Modules/Kerjasama/Resources/assets/sass/reports.scss')
    @endpush


    @if ($isUsingKop)
        @if (!empty($dataKop))
            <section class="kop-v1-container">
                {!! $dataKop !!}
            </section>
        @else
            <section>
                <div id="kop" class="kop">
                    <div class="logo">
                        <img src="{{ session('token.logo_univ') ?? Page::quantumAsset('images/logo-kampus.png') }}"
                            style="max-height: 70px;" alt="Logo Universitas" />
                    </div>
                    <div class="univ-info">
                        <h1>{{ $datav1['nama'] }}</h1>
                        <p>{{ $datav1['alamat'] }}</p>
                    </div>
                </div>
            </section>
        @endif
    @endif

    <div class="pb-3 pt-3">
        <h3 class="judul-laporan">Laporan Kerjasama</h3>
    </div>

    <div class="pb-3">
        <div style="display: flex; flex-direction: column; flex-wrap: wrap; width: 100%;">
            <div class="d-flex">
                <div style="width: 200px;">
                    Tanggal Kerjasama
                </div>
                <div style="">
                    @if (!empty($filter['tanggal_mulai_berlaku']))
                        : {{ Carbon::parse($filter['tanggal_mulai_berlaku']['value'])->translatedFormat('d F Y') }}
                        <b>s.d.</b>
                        {{ Carbon::parse($filter['tanggal_akhir_berlaku']['value'])->translatedFormat('d F Y') }}
                    @else
                        : Semua Tanggal Kerjasama
                    @endif
                </div>
            </div>

            @foreach ($filterShow as $show)
                <div class="d-flex">
                    <div style="width: 200px;">
                        {{ $show['label'] }}
                    </div>
                    <div>
                        : {{ $show['text'] ?? 'Semua ' . $show['label'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>


    <x-core::quantum-3.table.data :responsiveTable="false" :showNumber="true" :withSeparator="false" :$header :$data />

    @push('scripts')
        <script>
            function cetakLaporan() {
                window.print();
            }
        </script>
    @endpush

    </x-core::quantum-3.layouts.html>