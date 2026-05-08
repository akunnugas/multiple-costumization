@props([
    'canCreate' => true,
    'canDelete' => true,
    'isReference' => false,
    'syncLabel' => null,
    'withSync' => false,
    'showDeleteChecked' => true,
    'createLabel' => null,
    'customCreateUrl' => null,
])
@if ($withSync)
    <x-core::button href="javascript:void(0);" id="btn_sync" leading-icon="arrow-path">
        <span class="btn__text">{{ $syncLabel ?? 'Sinkronisasi Data' }}</span>
    </x-core::button>
@endif
@if ($canCreate)
    @php
        $attributes = [];
        if (!empty($isLivewire)) {
            $attributes['wire:click'] = 'showCreate';
        } elseif ($isReference) {
            $attributes['href'] = Page::buildURL(['create' => 1, 'edit' => null]);
        } elseif (!empty($customCreateUrl)) {
            $attributes['href'] = $customCreateUrl;
        } else {
            $attributes['href'] = Page::createURL();
        }

        $attributes = Page::buildAttributes($attributes);
    @endphp
    <x-core::button {{ $attributes }} leading-icon="plus" size="sm">
        <span class="btn__text">{{ $createLabel ?? 'Tambah Data' }}</span>
    </x-core::button>
@endif
@if ($canDelete && $showDeleteChecked)
    <x-core::button id="btn_delete" variant="destructive" leading-icon="trash-solid" class="btn_vr-right"
        :icon="true" />
@endif
