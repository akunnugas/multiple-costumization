<div class="text-center d-flex gap-1 justify-content-center">
    @if ($showDetail)
        <x-core::quantum-3.button leading-icon="eye-solid" variant="outline-secondary" icon="true" size="sm"
            :href="Page::detailURL($data['id'], $urlInfo ?? null)" />
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