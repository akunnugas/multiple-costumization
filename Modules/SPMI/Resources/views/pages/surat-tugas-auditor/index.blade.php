<x-core::layouts.list :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc :$title :$subtitle :$createLabel :$emptyState>
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Unggah dokumen Surat Tugas (ST) auditor dan pastikan setiap auditor telah dipetakan ke program studi atau unit kerja yang sesuai. Tambahkan atau perbarui data jika ada perubahan penugasan.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
