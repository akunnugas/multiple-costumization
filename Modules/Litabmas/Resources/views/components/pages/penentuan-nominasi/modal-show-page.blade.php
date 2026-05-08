@props(['idPengajuanPendanaan', 'sudahDinominasikan' => false])
@if (!$sudahDinominasikan)
    <x-core::modal title="Apakah Anda yakin menyatakan lolos nominasi untuk Proposal ini?" variant="primary"
        id="modal-status-lolos-nominasi">
        <x-core::form method="PUT"
            action="{{ route('litabmas.penentuan-nominasi.lolos-nominasi', $idPengajuanPendanaan) }}">
            <x-core::modal.body>
                Jika Anda yakin akan menyatakan lolos nominasi proposal, maka perubahan tidak dapat dibatalkan ketika
                sudah melewati batas akhir penilaian.
                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batalkan
                        </x-core::button>
                        <x-core::button variant="primary" type="submit">
                            Ya, Yakin
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>
    <x-core::modal title="Apakah Anda yakin menyatakan tidak lolos nominasi untuk Proposal ini?" variant="primary"
        id="modal-status-tidak-lolos-nominasi">
        <x-core::form method="PUT"
            action="{{ route('litabmas.penentuan-nominasi.tidak-lolos-nominasi', $idPengajuanPendanaan) }}">
            <x-core::modal.body>
                Jika Anda yakin akan menyatakan tidak lolos nominasi proposal, maka perubahan tidak dapat dibatalkan
                ketika sudah melewati batas akhir penilaian.
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
@else
    <x-core::modal title="Apakah Anda yakin menyatakan nominasi untuk Proposal ini?" variant="primary"
        id="modal-batalkan-status-nominasi">
        <x-core::form method="PUT"
            action="{{ route('litabmas.penentuan-nominasi.batalkan-status-nominasi', $idPengajuanPendanaan) }}">
            <x-core::modal.body>
                Jika Anda yakin akan membatalkan nominasi proposal, maka perubahan tidak dapat dibatalkan ketika sudah
                melewati batas akhir penilaian.
                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batalkan
                        </x-core::button>
                        <x-core::button variant="primary" type="submit">
                            Ya, batalkan
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>
@endif
