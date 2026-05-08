<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="false" :canDelete="false"
    :showNumber="$showNumber ?? false" :emptyState="$emptyState ?? []">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <h4 class="alert__heading">Tinjau dan Finalisasi RTM</h4>
                <p>
                    Periksa hasil temuan dan tindak lanjut dari setiap unit kerja. Klik Isi Tinjauan atau Perbarui
                    Tinjauan pada kolom Aksi untuk melengkapi hasil rapat.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
