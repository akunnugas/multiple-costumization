@props(['idPengajuanPendanaan', 'statusPendanaan' => false, 'modalData' => []])

{{-- @if ($statusPendanaan) --}}
<x-core::modal title="Apakah Anda yakin menyatakan bahwa proposal ini tidak lolos Pendanaan?" variant="primary"
    id="modal-penentuan-pendanaan-tidak-lolos">
    <x-core::form method="PUT"
        action="{{ route('litabmas.penentuan-pendanaan.status-tidak-lolos-pendanaan', $resourceId) }}">
        <x-core::modal.body>
            Jika proposal ini dinyatakan tidak lolos, maka tidak akan lolos untuk pendanaan.
            Perubahan tidak dapat dibatalkan setelah batas akhir penilaian.
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>
                    <x-core::button variant="destructive" type="submit">
                        Ya, Tidak Lolos
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>
{{-- @else
    <x-core::modal title="Apakah Anda yakin membatalkan penilaian proposal ini?" variant="primary" id="modal-batalkan-status-dokumen">
        <x-core::form method="PUT" action="{{ route('litabmas.penilaian-administrasi.batalkan-status-dokumen', $idPengajuanPendanaan) }}">
            <x-core::modal.body>
                Jika membatalkan penilaian akan mengahapus semua nilai Similarity dan AI serta menghapus Reviewer yang
                telah Anda tentukan.
                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Kembali
                        </x-core::button>
                        <x-core::button variant="primary" type="submit">
                            Ya, Batalkan Penilaian
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>
@endif --}}
