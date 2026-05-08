@props(['idPengajuanPendanaan', 'fields' => [], 'data' => null])
<x-core::modal title="Buat Jadwal Presentasi Proposal" variant="primary" id="modal-tambah-jadwal-presetasi-nominasi">
    <x-core::form method="PUT"
        action="{{ route('litabmas.penentuan-nominasi.update-jadwal-presentasi', $idPengajuanPendanaan) }}">
        <x-core::modal.body>
            <div class="modal__content-input">
                @foreach ($fields as $key => $item)
                    @php
                        if ($item['field'] === 'status') {
                            unset($fields[$key]);
                        }
                        $item['name'] ??= $item['field'];
                        $item['value'] ??= $data[$item['field']] ?? null;
                        unset($item['field']);

                        $attributes = Page::buildAttributes($item);
                    @endphp
                    <x-core::controls.form {{ $attributes }} />
                @endforeach
            </div>
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>
                    <x-core::button variant="primary" type="submit">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>
