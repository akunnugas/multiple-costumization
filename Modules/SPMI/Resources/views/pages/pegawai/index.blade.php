@php
    $title = $title ?? null;
    $subtitle = $subtitle ?? null;
@endphp

<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :emptyState="$emptyState ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :createLabel="$createLabel ?? null"
    :canCreate="$canCreate ?? false" :syncLabel="'Tarik Data Pegawai'">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data sumber diambil dari <b>Modul Kepegawaian</b>. Gunakan tombol Tarik Data Pegawai untuk memperbarui data
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
