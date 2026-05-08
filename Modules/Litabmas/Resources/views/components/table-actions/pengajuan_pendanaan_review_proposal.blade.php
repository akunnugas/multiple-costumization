@php use Modules\Core\Helpers\Format; @endphp
@props([
    'data' => [],
    'canUpdate' => false,
    'canDelete' => false,
    'header' => [],
    'definer' => null,
    'definerField' => null,
])

@php
    $encoded = base64_encode(
        json_encode([
            'id' => $data['id'],
            'text' => $data['nama_dokumen'],
            'temp_url' => $data['temp_url'],
            'last_version_size' => Format::formatBytes($data['last_version_size'])
        ]),
    );
@endphp

<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
    @if ($canUpdate && empty($data['apakah_asli']))
        <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                        href="javascript:editRecord('{{ $encoded }}')" />
    @else
        <x-core::button leading-icon="pencil-solid" variant="outline" size="xs" disabled />
    @endif
</div>
