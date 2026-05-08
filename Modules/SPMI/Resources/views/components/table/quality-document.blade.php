@props([
    'id' => null,
    'data' => [],
    'header' => [],
    'permission' => null,
    'sortable' => false,
    'sort' => null,
    'sortDesc' => null,
    'keySort' => null,
])
@php
    $resourceTitle ??= 'Data';
    $showCheck = false;
@endphp
<div {{ $attributes->merge(['class' => 'table-max']) }}>
    <table id="table-{{ isset($id) ? $id : null }}" class="table">
        @if (!empty($header))
            <thead>
                <tr>
                    @if (!empty($showCheck))
                        <th class="cell-check cell-center">
                            <input type="checkbox" class="check-all-item" name="group">
                        </th>
                    @endif
                    @foreach ($header as $i => $item)
                        @php
                            $no = $i + 1;
                            $sortableItem = $sortable && !empty($item['sortable']);
                            $attributes = Page::buildAttributes($item['attributes'] ?? null);

                            $label = $item['label'] ?? null;
                            if (empty($label) && !empty($item['field'])) {
                                $label = Page::defineLabelByField($item['field']);
                            }
                        @endphp
                        <th {{ $attributes->class([
                            'cell-sorting' => $sortableItem,
                            'cell-sorting_active-' . (empty($sortDesc) ? 'asc' : 'desc') => $sortableItem && $sort == $no,
                        ]) }}
                            data-no="{{ $no }}">
                            {{ $label }}
                        </th>
                    @endforeach
                    @if (!empty($permission))
                        <th class="cell-action cell-center">Aksi</th>
                    @endif
                </tr>
            </thead>
        @endif
        @if (!empty($data))
            <tbody>
                @foreach ($data as $row)
                    @php
                        $definer = null;
                    @endphp
                    <tr>
                        @if (!empty($showCheck))
                            <td class="cell-check cell-center">
                                <input type="checkbox" class="form-control__checkbox check-item" name="group[]"
                                    value="{{ $row['id'] }}">
                            </td>
                        @endif
                        @empty($header)
                            @foreach ($row as $item)
                                <td>{{ $item }}</td>
                            @endforeach
                        @else
                            @foreach ($header as $item)
                                @php
                                    $value = $row[$item['field']] ?? null;
                                    if (empty($definer) && !empty($item['definer'])) {
                                        $definer = $value;
                                    }

                                    // if type select get value by options
                                    if (!empty($item['options'])) {
                                        $value = $item['options'][$value];
                                    }

                                    $width = $item['width'] ?? null;

                                    // dynamic component
                                    $dynamicComponent = Page::defineFieldComponent($item);
                                @endphp
                                <td @if (isset($width)) style="width: {{ $width }}" @endif>
                                    @if (!empty($dynamicComponent))
                                        <x-dynamic-component :component="$dynamicComponent" :$value :data="$row" />
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        @endempty
                        @if (!empty($permission))
                            <td class="cell-action">
                                <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                    @if (!empty($permission['get']))
                                        <a href="{{ Page::detailURL($row['id']) }}"
                                            class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                            <span class="icon icon-eye-solid"></span>
                                        </a>
                                    @endif
                                    @if (!empty($permission['delete']))
                                        @php
                                            $encoded = base64_encode(
                                                json_encode([
                                                    'id' => $row['id'],
                                                    'text' => $row['text'] ?? $definer,
                                                ]),
                                            );
                                        @endphp
                                        <a href="javascript:deleteRecord('{{ $encoded }}')"
                                            class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                            <span class="icon icon-trash-solid"></span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        @endif
        <input class="table-id" type="hidden"
            value={{ Page::buildURL(["sort-type-$keySort" => '__sort__|__desc__']) }} />
    </table>
</div>
@pushOnce('scripts')
    <script>
        @if (!empty($sortable))
            const table = document.querySelectorAll(".table");
            table.forEach(element => {
                const cells = Array.from(element.getElementsByClassName("cell-sorting"));
                for (i in cells) {
                    cells[i].addEventListener("click", function(e) {
                        const sortDesc = e.target.classList.contains("cell-sorting_active-asc");

                        const no = e.target.getAttribute('data-no')
                        route = String(element.querySelector('.table-id').value)
                            .replace('__sort__', `no-${no}`)
                            .replace('__desc__', 'dir-' + (sortDesc ? 'desc' : 'asc'));

                        route = route.replace('|', '_');

                        window.location.href = route;
                    });
                };
            });
        @endif

        @if (!empty($permission['delete']))
            function deleteRecord(encoded) {
                const decoded = JSON.parse(atob(encoded));
                const modal = document.getElementById("modal_delete");
                const span = modal.querySelector("#span_text");

                if (decoded.text) {
                    span.style.fontWeight = "bold";
                    span.innerHTML = '"' + decoded.text + '"';
                } else {
                    span.style.fontWeight = "normal";
                    span.innerHTML = "tersebut";
                }

                modal.querySelector("form").action = "{{ Page::indexURL() }}/" + decoded.id;
                modal.classList.add("is-visible");
            }
        @endif
    </script>
@endPushOnce
