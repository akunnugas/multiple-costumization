<x-core::layouts.list :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc :withSync="$withSync ?? false"
        :syncMessage="$syncMessage ?? null" :showNumber="$showNumber ?? false" :staticAlert="$staticAlert ?? []" :showDeleteChecked="$showDeleteChecked ?? true" :syncLabel="'Tarik Data Periode'" :is-reference="true">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Data ini menampilkan daftar <b>periode pelaksanaan Audit Mutu Internal (AMI)</b> yang digunakan untuk
                    mengatur jadwal audit setiap tahun.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
