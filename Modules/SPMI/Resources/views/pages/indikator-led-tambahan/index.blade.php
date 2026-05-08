<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :title="$title" :subtitle="$subtitle" :withCustomAction="false" 
    :createLabel="'Tambah Komponen ED Tambahan'"
    :emptyState="[
        'title' => 'Belum Ada Data Komponen ED Tambahan',
        'subtitle' => 'Data akan muncul otomatis setelah versi standar akreditasi dipilih.',
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
                    Data ini menampilkan daftar <b>Komponen Evaluasi Diri (ED) Tambahan</b> yang digunakan untuk
                    melengkapi
                    komponen utama dalam penyusunan Laporan Evaluasi Diri (LED). Pastikan seluruh komponen ED tambahan
                    sudah sesuai dengan bidang dan versi standar akreditasi yang digunakan.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>

<livewire:spmi::upload-ikt-modal :jenisIndikator="'ed'" />
