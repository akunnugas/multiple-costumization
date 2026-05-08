@props([
    'data' => [],
    'header' => [],
    'showNumber' => false,
    'showCheck' => false,
    'showAction' => true
])
<tr {{ $attributes }}>
    @if (!empty($showCheck))
        <td class="cell-check cell-center">
            @if (!empty($data['id']))
                <x-core::checkbox.control class="check-item" name="group[]" value="{{ $data['id'] }}" />
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
                'padding-top:14px',
                'display: none' => $isHidden,
            ])>
                <x-core::controls.form {{ $attributes }} :show-label="false" />
            </td>
        @else
            <x-core::table.cell :$data :header="$item" />
        @endempty
    @endforeach
    @if($showAction)
        <td class="cell-action">
            <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                <x-core::button type="submit" leading-icon="check-solid" variant="outline" size="xs" />
                @php
                    $attributes = [];
                    if (!empty($isLivewire)) {
                        $attributes['wire:click'] = 'hideForm';
                    } else {
                        $attributes['href'] = Page::buildURL(['create' => null, 'edit' => null]);
                    }

                    $attributes = Page::buildAttributes($attributes);
                @endphp
                <x-core::button leading-icon="x-mark-solid" variant="outline" size="xs" {{ $attributes }} />
            </div>
        </td>
    @endif
</tr>
