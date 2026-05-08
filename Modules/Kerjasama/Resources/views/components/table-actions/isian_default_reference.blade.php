<div class="text-center d-flex gap-1 justify-content-center">
    @if ($canUpdate)
        @php
            $attributes = [];
            if (!empty($isLivewire)) {
                $attributes['wire:click'] = 'showEdit(' . $data['id'] . ')';
            } else {
                $attributes['href'] = Page::buildURL(['edit' => $data['id'], 'create' => null]);
            }

            $attributes = [
                ...$attributes,
                "href" => $data['isian_default'] ? null : $attributes['href'],
                "disabled" => $data['isian_default']
            ];

            $attributes = Page::buildAttributes($attributes);
        @endphp
        <x-core::quantum-3.button leading-icon="pencil-solid" variant="outline-secondary" icon="true" size="sm"
            {{ $attributes }} />
    @endif
    @if ($canDelete)
        @php
            foreach ($header as $item) {
                if ($definerField !== $item['field']) {
                    continue;
                }

                $options = $item['options'] ?? null;
                $value = $data['text'] ?? $definer;

                // Jika options berasal dari model dan value numerik
                if (!empty($options) && !is_array($options) && is_numeric($value)) {
                    $value = $options::optionValue($value);
                }

                // Jika options adalah array, ambil value berdasarkan key
                if (!empty($options) && is_array($options)) {
                    $value = $options[$value] ?? $value;
                }
            }

            $encoded = base64_encode(json_encode([
                'id' => $data['id'],
                'text' => $data['text'] ?? ($value ?? $definer),
            ]));

            // Menggunakan ComponentAttributeBag untuk menyusun atribut
            $attributes = new \Illuminate\View\ComponentAttributeBag([
                'disabled' => $data['isian_default'],
                'leading-icon' => 'trash-solid',
                'variant' => 'outline-secondary',
                'icon' => 'true',
                'size' => 'sm',
                'href' => !$data['isian_default'] ? "javascript:deleteRecord('$encoded')" : '',
                'data-btn-label' => 'Hapus',
            ]);

        @endphp
        
        <x-core::quantum-3.button 
            {{ $attributes }} 
            leading-icon="trash-solid" 
            variant="outline-secondary"
            icon="true" 
            size="sm" 
            data-btn-label="Hapus" 
        />
    @endif
</div>