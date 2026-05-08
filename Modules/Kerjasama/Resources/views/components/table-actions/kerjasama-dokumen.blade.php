<div class="text-center d-flex gap-1 justify-content-center">
@php
    $data = $attributes['data'];

    $id = $data['has_dokumen'];



    

@endphp
@if ($id)
   
    <x-core::quantum-3.button 
        leading-icon="file-solid" 
        variant="outline-secondary" 
        icon="true" 
        size="sm" 
        :href="route('kerjasama.dokumen', ['kegiatan' => $data['id']])" 
    />
@endif


    @if ($showDetail)
<x-core::quantum-3.button 
    leading-icon="eye-solid" 
    variant="outline-secondary" 
    icon="true" 
    size="sm" 
    :href="route('kerjasama.data-kerjasama.show', ['data_kerjasama' => $data['id']]) . '?backUrl=' . url()->current()" 
/>    @endif
    @if ($canUpdate)
        @php
            $attributes = [];
            if (!empty($isLivewire)) {
                $attributes['wire:click'] = 'showEdit(' . $data['id'] . ')';
            } elseif (!empty($isEditInline)) {
                $attributes['href'] = Page::buildURL(['edit' => $data['id'], 'create' => null]);
            } else {
                $attributes['href'] = Page::editURL($data['id']);
            }

            $attributes = Page::buildAttributes($attributes);
        @endphp
        <x-core::quantum-3.button leading-icon="pencil-solid" variant="outline-secondary" icon="true" size="sm"
            {{ $attributes }} />
    @endif
    @if ($canDelete)
        @php
            // NOTE: Untuk mengambil value dari field yang dijadikan definer, ketika menggunakan option model
            foreach ($header as $item) {
                if ($definerField !== $item['field']) {
                    continue;
                }

                $options = $item['options'] ?? null;

                $value = $data['text'] ?? $definer;

                // options diambil dari model
                if (!empty($options) && !is_array($options) && is_numeric($value)) {
                    // jika $value bukan int maka tidak perlu diubah dari optionValue()
                    $value = $options::optionValue($value);
                }

                // get value by options
                if (!empty($options) && is_array($options)) {
                    $value = $options[$value];
                }
            }

            $encoded = base64_encode(
                json_encode([
                    'id' => $data['id'],
                    'text' => $data['text'] ?? ($value ?? $definer),
                ]),
            );
        @endphp
        <x-core::quantum-3.button leading-icon="trash-solid" variant="outline-secondary" icon="true" size="sm"
            href="javascript:deleteRecord('{{ $encoded }}')" data-btn-label="Hapus" />
    @endif
</div>