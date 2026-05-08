<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :emptyState="[
        'title' => 'Belum Ada Data Buku Akreditasi',
        'subtitle' => 'Tambahkan buku akreditasi baru atau sinkronkan data dari sistem untuk mulai mengelola dokumen LED dan LKPS.',
    ]" :createLabel="'Tambah Buku Akreditasi'">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Kelola daftar buku akreditasi yang digunakan dalam proses Audit Mutu Internal dan simulasi
                    akreditasi SPME.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
