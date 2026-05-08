<x-core::layouts.list :$data :$header :$create :$edit :$filter :$search :$sort :$sortDesc :$title :$subtitle :$createLabel :$emptyState>
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Gunakan tombol <b>Buat Jadwal</b> untuk menetapkan jadwal pelaksanaan audit di setiap program studi atau unit kerja. Pastikan periode AMI, tanggal pengisian, dan tanggal penilaian telah sesuai sebelum status diaktifkan.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
</x-core::layouts.list>
