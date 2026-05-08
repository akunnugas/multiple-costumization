<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="false" :canDelete="false" :showNumber="$showNumber ?? false" :emptyState="$emptyState ?? []">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Lengkapi penilaian mandiri untuk setiap indikator sesuai panduan yang digunakan. Periksa kembali progres penilaian Anda dan pastikan seluruh data sudah terisi hingga 100%.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
