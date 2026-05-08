@props([
    'data' => [],
    'canUpdate' => false,
    'header' => [],
    'definer' => null,
    'definerField' => null,
])

@php
    $encoded = base64_encode(
        json_encode([
            'id_jenis_output_penelitian' => $data['id_jenis_output_penelitian'],
            'nama_output' => $data['nama_output'],
        ]),
    );
@endphp

<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
    @if ($canUpdate)
        <x-core::button leading-icon="arrow-up-tray" variant="outline" size="xs"
                        href="javascript:showModalUpload('{{ $encoded }}')" />
    @else
        <x-core::button leading-icon="arrow-up-tray" variant="outline" size="xs" disabled />
    @endif
</div>
