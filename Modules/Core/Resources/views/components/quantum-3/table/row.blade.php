@props([
    'canDelete' => false,
    'canUpdate' => false,
    'data' => [],
    'definer' => null,
    'header' => [],
    'readonly' => false,
    'showAction' => false,
    'showCheck' => false,
    'showNumber' => false,
    'showDetail' => false,
    'actComponent' => null,
    'isEditInline' => true,
])
@php
    if ($readonly) {
        $canDelete = false;
        $canUpdate = false;
    }

    // check for tdspan
    $ignoredRowspan = [];
    foreach ($header as $headIndex => $item) {
        if (isset($item['tdspan']) && $item['tdspan']) {
            $ignoredRowspan[] = $item['field'];
            $raw = $data[$item['field']];
            $jsonish = preg_replace(['/^\s*\{/', '/\}\s*$/'], ['[', ']'], $raw, 1);
            $jsonArray = json_decode($jsonish, true);
            $data[$item['field']] = $jsonArray[0];
            unset($jsonArray[0]);
        }

        $header[$headIndex]['_index'] = $headIndex;
        $header[$headIndex]['_rowspan'] = 1;
    }

    // get rowspan
    if (isset($jsonArray)) {
        foreach ($header as $headIndex => $item) {
            if (in_array($item['field'], $ignoredRowspan)) {
                $header[$headIndex]['_rowspan'] = 1;
            } else {
                $header[$headIndex]['_rowspan'] = count($jsonArray) + 1;
            }
        }
    }
    $tableLastHeader = end($header);
@endphp
<tr>
    @if (!empty($showCheck))
        <td rowspan="{{ $tableLastHeader['_rowspan'] }}" class="cell-check text-center">
            @if (empty($readonly))
                <input type="checkbox" class='form-check-input' name="group[]"
                    value="{{ isset($data['datatype']) ? $data['id'] . ',' . $data['datatype'] : $data['id'] }}"
                    data-type="{{ $data['datatype'] ?? null }}" x-on:change="$store.checkbox.toggleCheck('{{ $data['id'] }}')" x-bind:checked="$store.checkbox.selectCheck.includes('{{ $data['id'] }}')" >
            @endif
        </td>
    @endif
    @if (!empty($showNumber))
        <td class="cell-check text-center">
            {{ $data['_numberRow'] ?? null }}
        </td>
    @endif
    @empty($header)
        @foreach ($data as $item)
            <td>{{ $item }}</td>
        @endforeach
    @else
        @foreach ($header as $item)
            @php
                $isHidden = !empty($item['type']) && $item['type'] === 'hidden';
                if ($isHidden) {
                    continue;
                }

                if (empty($definer) && !empty($item['definer'])) {
                    $definer = $data[$item['field']] ?? null;
                    $definerField = $item['field'];
                }
            @endphp
            <x-core::quantum-3.table.cell :$data :header="$item" />
        @endforeach
    @endempty

    @if (!empty($actComponent) || !empty($showAction))
        @if ($showAction && !isset($actComponent))
            <td rowspan="{{ $tableLastHeader['_rowspan'] }}" class="cell-action text-nowrap">
                <div class="text-center d-flex gap-1 justify-content-center">
                    @if ($showDetail)
                        <x-core::quantum-3.button leading-icon="eye-solid" variant="outline-secondary" icon="true" size="sm" :href="Page::detailURL($data['id'], $urlInfo ?? null)" />
                    @endif
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
            </td>
        @else
            <td class="cell-action text-nowrap">
                @php
                    $dynamicComponent = Page::defineTableActionComponent(['component' => true, ...$actComponent]);
                @endphp
                <x-dynamic-component :component="$dynamicComponent" :$showDetail :$canUpdate :$canDelete :$data :$header :$definerField :$definer />
            </td>
        @endif
    @endif
</tr>

@if (isset($jsonArray) && !empty($jsonArray))
    @foreach ($jsonArray as $val)
        <tr>
            <td>{{ $val }}</td>
        </tr>
    @endforeach
@endif

@if ($canDelete)
    @pushOnce('scripts')
        <script>
            function deleteRecord(encoded) {
                List.deleteRecord(encoded, "{{ Page::indexURL() }}", 'modal_delete_row');
            }
        </script>
    @endPushOnce
@endif
