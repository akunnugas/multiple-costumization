@props([
    'data' => [],
    'header' => [],
    'showNumber' => false,
    'showCheck' => false,
])
<tr {{ $attributes }}>
    @if (!empty($showCheck))
        <td class="cell-check text-center">
            @if (!empty($data['id']))
                <input type="checkbox" {{ $attributes->class(['form-check-input']) }}  name="group[]" value="{{ $data['id'] }}">
            @endif
        </td>
    @endif
    @if(!empty($showNumber))
        <td class="cell-check cell-center">{{ $data['_numberRow'] ?? null }}</td>
    @endif
    @foreach ($header as $item)
        @php
            $isHidden = (!empty($item['type']) && $item['type'] === 'hidden');
        @endphp
        @empty($item['readonly'])
            @php
                $activeField = $item['name'] ?? $item['field'];
                if (isset($item['key_field'])) {
                    $activeField = $item['key_field'];
                }

                $item += [
                    'name' => $item['name'] ?? $item['field'] ?? $activeField,
                    'value' => $data[$activeField] ?? null,
                ];

                if (!empty($isLivewire)) {
                    $item['wire:model'] = 'record.' . $activeField;
                }

                $attributes = Page::buildAttributes($item);
            @endphp
            <td @style([
                'display: none' => $isHidden,
            ])>
                <x-core::quantum-3.controls.form {{ $attributes }} :show-label="false" />
            </td>
        @else
            <x-core::quantum-3.table.cell :$data :header="$item" />
        @endempty
    @endforeach
    <td class="cell-action text-nowrap">
        <div class="text-center d-flex gap-1">
            <x-core::quantum-3.button type="submit" leading-icon="check-solid" variant="outline-secondary"  icon="true" size="sm" />
            @php
                $attributes = [];
                if (!empty($isLivewire)) {
                    $attributes['wire:click'] = 'hideForm';
                } else {
                    $attributes['href'] = Page::buildURL(['create' => null, 'edit' => null]);
                }

                $attributes = Page::buildAttributes($attributes);
            @endphp
            <x-core::quantum-3.button leading-icon="x-close-solid" variant="outline-secondary"  icon="true" size="sm" {{ $attributes }} />
        </div>
    </td>
</tr>
