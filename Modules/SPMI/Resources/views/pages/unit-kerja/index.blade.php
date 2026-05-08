@php
    $title = $title ?? null;
    $subtitle = $subtitle ?? null;
    $isActiveHR = $isActiveHR ?? false;

    $canCreate = false;
    if (!$isActiveHR) {
        $canCreate = true;
    }
@endphp

<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :emptyState="$emptyState ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :createLabel="$createLabel ?? null"
    :canCreate="$canCreate ?? false" :syncLabel="'Tarik Data Unit Kerja'" :syncMessage="'Apakah Anda yakin ingin melakukan penarikan data unit kerja?'" :syncTitle="'Tarik Data Unit Kerja'" :createLabel="'Tambah Unit Kerja Manual'">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    @if ($isActiveHR)
                        Data sumber diambil dari <b>Modul Kepegawaian (Unit Kerja Non Akademik) dan Modul Akademik (Program Studi untuk Unit Akademik)</b>
                    @else
                        Data ini bersumber dari Modul Akademik melalui menu <b>Data Pelengkap → Perguruan Tinggi → Program
                        Studi</b>
                    @endif
                    . Gunakan tombol Sinkronisasi Data Unit Kerja untuk memperbarui data sesuai sistem akademik
                        terbaru.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
