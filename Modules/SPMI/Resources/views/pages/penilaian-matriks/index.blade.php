<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data berikut menampilkan daftar <b>elemen, dimensi, dan indikator</b> yang digunakan untuk menilai capaian
                    Indikator Kinerja Utama (IKU) sesuai panduan akreditasi. Pastikan setiap indikator memiliki <b>bobot
                    penilaian dan status aktif</b> agar hasil audit dapat dihitung secara akurat.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
