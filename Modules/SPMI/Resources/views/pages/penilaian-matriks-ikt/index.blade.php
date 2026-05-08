@php
    $isErrorImport = session()->has('import_error_file') && session()->has('error');
    if ($isErrorImport) {
        $message = session('error');
        session()->forget('error');
    }
@endphp
<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :withCustomAction="false">
    <x-slot:customAction>
        <x-core::button variant="outline" id="btn_import" leading-icon="document-arrow-down"
            onclick="Livewire.dispatch('showUploadIktModal')">
            <span class="btn__text">Import Data</span>
        </x-core::button>
    </x-slot:customAction>
    <x-slot:tableHeader>
        @if ($isErrorImport)
            <div class="alert alert alert_danger" style="margin-bottom: 1rem">
                <div class="alert__content">
                    <p>
                        {{ $message }}
                        <b>
                            <u>
                                <a href="{{ route('spmi.penilaian-matriks-ikt.download-error', ['file' => session('import_error_file')]) }}">
                                    Klik disini
                                </a>
                            </u>
                        </b>
                    </p>
                </div>
                <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
            </div>
        @endif
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data berikut menampilkan daftar <b>elemen, dimensi, dan indikator tambahan</b> yang digunakan untuk menilai
                    capaian Indikator Kinerja Tambahan (IKT). Pastikan setiap indikator memiliki <b>bobot dan status aktif</b>
                    agar dapat diperhitungkan secara akurat dalam hasil audit mutu internal.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>

<livewire:spmi::upload-penilaian-ikt-modal />
