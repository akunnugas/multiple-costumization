<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="false" :canDelete="false"
    :showNumber="$showNumber ?? false" :emptyState="$emptyState ?? []">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Lengkapi Laporan Evaluasi Diri (ED) untuk setiap program studi atau unit kerja sesuai panduan yang
                    digunakan. Pastikan seluruh indikator telah diisi dengan data dan bukti yang valid.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
