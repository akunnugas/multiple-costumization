<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="false" :canDelete="false"
    :showNumber="$showNumber ?? false" :emptyState="$emptyState ?? []">
    <x-slot:tableHeader>
        @php
            $userRole = auth()->user()->kode_role;
        @endphp
        @if ($userRole !== \Modules\Gate\Models\Role::ROLE_AUDITEE)
            <div class="alert alert alert_helper">
                <div class="alert__content">
                    <p>
                        Periksa progres penilaian setiap program studi, lalu berikan skor pada tiap indikator sesuai hasil audit untuk menghasilkan nilai akhir AMI.
                    </p>
                </div>
                <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
            </div>
            <br>
        @endif
    </x-slot:tableHeader>
</x-core::layouts.list>
