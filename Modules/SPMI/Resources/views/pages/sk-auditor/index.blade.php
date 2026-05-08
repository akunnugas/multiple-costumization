<x-core::layouts.list :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc :$title :$subtitle :$createLabel :$emptyState>
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Unggah dokumen Surat Keputusan (SK) auditor dan pastikan data auditor sesuai dengan periode serta tugas auditnya. Tambahkan atau perbarui auditor bila ada perubahan tim.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
