<x-core::layouts.list :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc :is-reference="true" :$title
    :$subtitle :emptyState="[
        'title' => 'Belum ada data peringkat mutu. ',
        'subtitle' => 'Silakan tambahkan peringkat baru sesuai ketentuan nilai mutu di perguruan tinggi Anda.',
    ]">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Tetapkan peringkat mutu SPMI berdasarkan rentang nilai yang berlaku di Perguruan Tinggi. Peringkat
                    ini akan digunakan sebagai dasar penentuan capaian mutu unit kerja berdasarkan hasil Audit Mutu
                    Internal.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
