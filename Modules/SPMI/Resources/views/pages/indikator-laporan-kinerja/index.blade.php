<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :emptyState="[
        'title' => 'Belum Ada Data Laporan Kinerja Utama',
        'subtitle' => 'Data Laporan Kinerja (LK) akan muncul otomatis sesuai versi standar akreditasi yang digunakan.',
    ]">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data ini menampilkan daftar <b>Laporan Kinerja (LK) Utama</b> yang digunakan sebagai acuan penyusunan
                    Laporan Kinerja. Pastikan seluruh indikator LK mencakup butir penilaian untuk setiap program studi
                    yang diaudit.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
