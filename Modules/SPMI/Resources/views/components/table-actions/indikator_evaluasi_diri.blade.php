@php
    $encoded = base64_encode(
        json_encode([
            'id' => $data['id'],
            'text' => $data['nama_indikator_evaluasi_diri'] ?? ($value ?? $definer),
        ]),
    );
@endphp
<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;"><a
        href="{{ route('spmi.indikator-evaluasi-diri.show', $data['id']) }}" class="btn btn_outline btn_xs"
        style="">
        <span class="icon icon-eye-solid"></span>
    </a>
     @if (request()->permission['put'] && !$data['apakah_data_default'])
        <x-core::button leading-icon="pencil-solid" variant="outline" size="xs" :href="Page::detailURL($data['id'], $urlInfo ?? null) . '/edit'" />
    @endif
    @if (!$data['apakah_data_default'])
        <a href="javascript:deleteRecord('{{ $encoded }}')" class="btn btn_outline btn_xs" data-btn-label="Hapus"
            style=""><span class="icon icon-trash-solid"></span></a>
    @endif
</div>
