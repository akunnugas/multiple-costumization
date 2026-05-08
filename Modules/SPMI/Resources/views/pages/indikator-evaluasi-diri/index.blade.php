<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :emptyState="[
        'title' => 'Belum Ada Data Komponen ED Utama',
        'subtitle' => 'Data akan muncul otomatis sesuai versi standar akreditasi yang digunakan.',
    ]">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data ini menampilkan daftar <b>Komponen Evaluasi Diri (ED) Utama</b> yang digunakan dalam penyusunan
                    Laporan Evaluasi Diri (LED). Pastikan seluruh komponen ED mencakup indikator kinerja utama yang
                    relevan dengan program studi yang diaudit.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
