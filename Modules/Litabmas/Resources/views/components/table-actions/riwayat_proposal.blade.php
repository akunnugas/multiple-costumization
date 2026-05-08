@props([
    'data' => [],
    'showDetail' => false,
    'canDelete' => false,
    'header' => [],
    'definer' => null,
    'definerField' => null,
])

@php
    $url = route('litabmas.riwayat-proposal.informasi-proposal.show', $data['id']);
@endphp

<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
    @if ($showDetail)
        <x-core::button leading-icon="eye-solid" variant="outline" size="xs" :href="$url" />
    @endif
</div>
