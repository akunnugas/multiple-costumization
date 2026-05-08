<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="true" :canDelete="true"
    :showNumber="$showNumber ?? false" :emptyState="[
        'title' => 'Belum Ada Data Instrumen Penilaian',
        'subtitle' => 'Tambahkan instrumen penilaian agar proses audit mutu internal dapat menggunakan acuan yang sesuai.',
    ]">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>

                    Data berikut menampilkan daftar instrumen penilaian audit mutu internal yang digunakan untuk proses
                    evaluasi sesuai jenis akreditasi dan jenjang program studi. Pastikan instrumen yang digunakan sudah
                    berlaku dan sesuai dengan standar yang ditetapkan.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
