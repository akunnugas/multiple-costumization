@php
    $title = $title ?? null;
    $subtitle = $subtitle ?? null;
@endphp

<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :emptyState="$emptyState ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :createLabel="$createLabel ?? null"
    :canCreate='false' :syncLabel="'Tarik Data Jenjang Pendidikan'">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data ini berasal dari modul Akademik melalui menu <b>Data Pelengkap → Perguruan Tinggi → Tingkat
                    Pendidikan</b>.Gunakan tombol Sinkronisasi Data untuk memperbarui informasi jenjang pendidikan sesuai
                    data terbaru dari sistem akademik.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
