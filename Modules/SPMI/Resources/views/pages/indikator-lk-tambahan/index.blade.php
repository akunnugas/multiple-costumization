<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :withCustomAction="false" :emptyState="[
        'title' => 'Belum Ada Data Laporan Kinerja Tambahan',
        'subtitle' => 'Data LK Tambahan akan muncul otomatis setelah versi standar akreditasi dipilih.',
    ]">
    <x-slot:customAction>
        <x-core::button onclick="Livewire.dispatch('showUploadPengisianIktModal')" variant="outline" id="btn_import"
            leading-icon="document-arrow-down">
            <span class="btn__text">Import Data</span>
        </x-core::button>
    </x-slot:customAction>
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data ini menampilkan daftar <b>Laporan Kinerja (LK) Tambahan</b> yang digunakan untuk melengkapi
                    indikator
                    kinerja utama dalam laporan kinerja program studi. Pastikan seluruh indikator telah sesuai dengan
                    bidang dan versi standar akreditasi yang digunakan.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>

<livewire:spmi::upload-ikt-modal :jenisIndikator="'lk'" />
