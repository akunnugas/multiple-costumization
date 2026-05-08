@props([
    'data' => [],
    'showDetail' => false,
    'canDelete' => false,
    'header' => [],
    'definer' => null,
    'definerField' => null,
])

<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
    <x-core::button leading-icon="eye-solid" variant="outline" size="xs"
        :href="route('litabmas.penilaian-reviewer.show', $data['id']) . '/overview'"
    />
</div>
