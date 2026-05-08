
@php
    $encoded = base64_encode(
        json_encode([
            'id' => $data['id'],
            'text' => $data['nama_singkat_penilaian_panduan'] ?? ($value ?? $definer),
        ]),
    );
@endphp
<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;"><a
        href="{{ route('spmi.penilaian-panduan.show', $data['id']) }}" class="btn btn_outline btn_xs"
        style="">
        <span class="icon icon-eye-solid"></span>
    </a>
    @if (request()->permission['put'] && !$data['apakah_data_default'])
        <x-core::button leading-icon="pencil-solid" variant="outline" size="xs" :href="Page::detailURL($data['id'], $urlInfo ?? null) . '/edit'" />
    @elseif(request()->permission['put'])
        <a href="#" class="btn btn_outline btn_xs disabled"
            style=""><span class="icon icon-pencil-solid"></span></a>
    @endif
    @if (request()->permission['delete'] && !$data['apakah_data_default'])
        <a href="javascript:deleteRecord('{{ $encoded }}')" class="btn btn_outline btn_xs" data-btn-label="Hapus"
            style=""><span class="icon icon-trash-solid"></span></a>
    @elseif(request()->permission['delete'])
        <a href="#" class="btn btn_outline btn_xs disabled"
            style=""><span class="icon icon-trash-solid"></span></a>
    @endif
</div>
