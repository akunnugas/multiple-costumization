<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :emptyState="[
        'title' => 'Belum Ada Data Syarat Akreditasi',
        'subtitle' =>
            'Data syarat akreditasi akan muncul otomatis sesuai dengan standar penilaian yang digunakan.',
    ]" :createLabel="'Tambah Syarat Akreditasi'">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>

                    Data ini menampilkan daftar <b>indikator penilaian dan syarat minimum akreditasi</b> yang digunakan
                    sebagai
                    acuan dalam pelaksanaan Audit Mutu Internal dan simulasi SPME.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
