<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="false" :canDelete="false"
    :showNumber="$showNumber ?? false" :emptyState="$emptyState ?? []">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <h4 class="alert__heading">Tinjau dan Lengkapi Temuan Auditor</h4>
                <p>
                    Periksa hasil audit untuk setiap program studi. Jika ada temuan yang belum tercatat, klik Isi atau
                    Ubah Temuan pada kolom Aksi untuk memperbarui data.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
