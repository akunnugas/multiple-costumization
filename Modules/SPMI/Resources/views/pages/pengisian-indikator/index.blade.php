<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="false" :canDelete="false"
    :showNumber="$showNumber ?? false" :emptyState="$emptyState ?? []">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Lengkapi data laporan kinerja untuk setiap indikator penilaian AMI dan unggah bukti kinerja
                    pendukung. Pastikan seluruh indikator sudah terisi sesuai panduan yang digunakan.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
