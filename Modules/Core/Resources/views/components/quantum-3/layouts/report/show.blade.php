@props([
    'title' => null,
    'pageBack' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
@endphp
<x-core::quantum-3.layouts.html title="Laporan Kerjasama">
    <header class="qn-header z-1 sticky-top p-md-3 py-2 py-md-2 border-bottom bg-white no-print">
        <div class="container-fluid d-grid d-flex justify-content-between align-items-center position-relative">
            <x-core::quantum-3.button href="{{ $pageBack ?? '' }}" variant="light" size="lg"
                leadingIcon="arrow-narrow-left">
                Kembali
            </x-core::quantum-3.button>
            <nav class="position-absolute top-50 start-50 translate-middle" aria-label="breadcrumb">
                {{ $title }}
            </nav>
            <div>
                <x-core::quantum-3.button onclick="cetakLaporan()" size="lg" leadingIcon="printer">
                    Cetak
                </x-core::quantum-3.button>
            </div>
        </div>
    </header>

    <div class="content-laporan">
        {{ $slot }}
    </div>
</x-core::quantum-3.layouts.html>


