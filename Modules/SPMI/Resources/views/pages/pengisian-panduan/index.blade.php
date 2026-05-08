<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="true" :canDelete="true"
    :showNumber="$showNumber ?? false" :emptyState="[
        'title' => 'Belum Ada Data Instrumen Pengisian',
        'subtitle' => 'Tambahkan instrumen baru atau pastikan data sudah tersinkronisasi dengan modul akreditasi.',
    ]">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data ini menampilkan daftar instrumen pengisian Laporan Kinerja (LK) dan Lembar Evaluasi Diri (LED)
                    yang digunakan dalam proses Audit Mutu Internal dan akreditasi. Pastikan instrumen yang digunakan
                    sesuai dengan lembaga akreditasi dan versi standar yang berlaku.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
