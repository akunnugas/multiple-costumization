<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :canCreate="false" :emptyState="$emptyState ?? []">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <h4 class="alert__heading">Tinjau Skor Akhir AMI</h4>
                <p>
                    Lihat hasil rekapitulasi skor akhir setiap unit kerja untuk menilai capaian mutu. Klik <b>Lihat Detail</b> pada kolom Aksi untuk meninjau rincian skor dan temuan audit.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
