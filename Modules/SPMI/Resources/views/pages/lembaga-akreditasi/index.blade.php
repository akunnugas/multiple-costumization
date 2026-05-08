<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false" :showNumber="$showNumber ?? false"
    :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data ini merupakan daftar lembaga akreditasi resmi yang terdaftar di Indonesia, digunakan sebagai
                    acuan dalam pelaksanaan audit dan simulasi akreditasi.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
