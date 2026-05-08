<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :emptyState="$emptyState ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :createLabel="$createLabel ?? null"
    :canCreate="true">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data standar audit digunakan sebagai dasar penilaian dalam proses Audit Mutu Internal (AMI).
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>