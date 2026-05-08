@props([
    'button' => null,
    'message' => null,
])

@php
    $isGetDataKurikulum = $attributes->get('iskurikulum', false);
    unset($attributes['iskurikulum']);
    $attributes = $attributes->merge([
        'title' => 'Tarik Data ' . ($resourceTitle ?? 'Data'),
        'formMethod' => 'POST',
        'variant' => 'primary',
    ]);
@endphp
<x-core::modal {{ $attributes }}>
    <x-core::modal.body>
        @if ($isGetDataKurikulum)
            <div style="margin-bottom: 10px;">Silakan pilih Tahun Kurikulum yang akan dilaporkan</div>
            @php
                list($err, $arr) = (new Modules\Core\Helpers\SiakadV1)->getDataFromView('akreditasicloud.v_tahunkurikulumprodi', [], 'distinct idkurikulum', 'idkurikulum desc');
                $arrKurikulum = [];
                foreach ($arr as $key => $value) {
                    $arrKurikulum[$value['idkurikulum']] = $value['idkurikulum'];
                }
            @endphp
            <x-core::select id="select_idkurikulum" name="idkurikulum" label="Tahun Kurikulum" :options="$arrKurikulum"
                onchange="" />
                <span class="util_d-none" style="color: red;" id="select_idkurikulum_error"></span>
        @else
            {!! $message ? $message : ('Apakah Anda yakin tarik data ' . strtolower($resourceTitle ?? null)) !!}
        @endif
        <x-slot:footer>
            <div class="grid cols-1 cols-sm-2">
                <x-core::button variant="outline" data-dismiss="modal">
                    Batal
                </x-core::button>
                <x-core::button variant="primary" id="btn_filling_getdata_checked">
                    {{ $button ?? 'Tarik' }}
                </x-core::button>
            </div>
        </x-slot:footer>
    </x-core::modal.body>
</x-core::modal>
