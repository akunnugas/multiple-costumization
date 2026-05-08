@props([
    'canUpdate' => true,
    'data' => [],
    'edit' => null,
    'filter' => [],
    'header' => [],
    'menu' => [],
    'sort' => null,
    'sortDesc' => null,
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'indikatorBobot' => 0,
])
@php
    if ($canUpdate && empty($permission['put'])) {
        $canUpdate = false;
    }

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Daftar ' . $title;
    }

    // form
    $action = $method = null;
    $editURL = Page::buildURL(['edit' => true]);

    if (isset($edit) && $edit != 1) {
        $edit = null;
    }

    if (!empty($edit)) {
        $action = '';
    }
@endphp
<x-core::layouts.main :$menu :$title :$subtitle>
    @push('head')
        <style>
            .alert.alert_helper::before {
                display: none;
            }

            .hidden-row {
                display: none;
            }
        </style>
    @endpush
    @if ($submenu)
        <x-slot:sidebar>
            <x-core::layouts.outer.sidebar :data="$submenu" />
        </x-slot:sidebar>
    @endif
    <div class="card card_table">
        <div class="card__body">
            <x-core::table>
                <x-slot:header>
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
                        <x-core::input type="search" value="{{ request()->get('search') }}" placeholder="Cari data ..."
                            style="width: 300px;" oninput="filterData(event.target.value)" />
                        <div class="box-table__wrapper" style="width: 300px;">
                            @foreach ($filter as $key => $item)
                                <x-core::select variant="search" name="{{ $key }}" label="{{ $item['label'] }}"
                                    :options="$item['options']" :selected="$item['selected']"
                                    onchange="setFilter('{{ $key }}', event.target.value)" />
                            @endforeach
                        </div>
                    </div>
                    <script>
                        function setFilter(key, value) {
                            const url = new URL(window.location.href);
                            url.searchParams.set(key, value);
                            window.location.href = url.href;
                        }

                        function filterData(value) {
                            const searchValue = value.toLowerCase().trim();
                            const tableBody = document.querySelector('table tbody');
                            if (!tableBody) return;

                            const allRows = Array.from(tableBody.querySelectorAll('tr'));

                            if (searchValue === '') {
                                allRows.forEach(row => {
                                    row.style.display = '';
                                });
                                return;
                            }

                            const rowsToShow = new Set();
                            allRows.forEach(row => {
                                const cells = row.querySelectorAll('td');
                                let rowText = '';

                                for (let i = 0; i < cells.length - 1; i++) {
                                    rowText += cells[i].textContent.toLowerCase() + ' ';
                                }

                                if (rowText.includes(searchValue)) {
                                    rowsToShow.add(row);

                                    let current = row;
                                    while (current && current.dataset.parentId) {
                                        const parentId = current.dataset.parentId;
                                        const parentRow = tableBody.querySelector(`tr[data-id="${parentId}"]`);
                                        if (parentRow) {
                                            rowsToShow.add(parentRow);
                                            current = parentRow;
                                        } else {
                                            break;
                                        }
                                    }
                                }
                            });

                            allRows.forEach(row => {
                                if (rowsToShow.has(row)) {
                                    row.style.display = '';
                                } else {
                                    row.style.display = 'none';
                                }
                            });
                        }
                    </script>
                    @if (empty($isLivewire))
                        @pushOnce('scripts')
                            <script type="module">
                                List.eventSearch(
                                    "{!! Page::buildURL(['search' => '__', 'page' => null, 'create' => null, 'edit' => null]) !!}",
                                    "{!! Page::buildURL(['search' => null, 'page' => null, 'create' => null, 'edit' => null]) !!}"
                                );
                            </script>
                        @endPushOnce
                    @endif
                </x-slot:header>
                <div class="alert alert alert_helper">
                    <div class="alert__content">
                        <p>

                            Data berikut menampilkan hasil <b>mapping antara panduan penilaian dan unit kerja</b> yang
                            digunakan dalam proses audit mutu internal. Pastikan seluruh program studi memiliki status
                            <b>Sudah Mapping</b> agar penilaian dapat dilakukan dengan benar.
                        </p>
                    </div>
                    <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
                </div>
                <br>
                <x-core::layouts.html.alert style="margin-bottom:1rem" />
                <x-core::form id="form_list" :$action :$method>
                    <div class="table-max">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($header as $i => $item)
                                        @php
                                            $no = $i + 1;
                                            $attributes = Page::buildAttributes($item['attributes'] ?? null);

                                            $label = $item['label'] ?? null;
                                            if (empty($label) && !empty($item['field'])) {
                                                $label = Page::defineLabelByField($item['field']);
                                            }
                                        @endphp
                                        <th data-no="{{ $no }}">
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                    <th style="width: 100px;">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @include('spmi::pages.mapping-matriks-penilaian.partials.table-row', [
                                    'nodes' => $data,
                                    'header' => $header,
                                ])
                            </tbody>
                        </table>
                    </div>
                </x-core::form>
            </x-core::table>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const form = document.getElementById('form_list');
                const saveData = document.getElementById('save-data');
                if (form && saveData) {
                    saveData.addEventListener('click', () => {
                        form.submit();
                    });
                }
            })()
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tbody = document.querySelector('table tbody');

                if (tbody) {
                    tbody.addEventListener('click', function(event) {
                        const parentRow = event.target.closest('.parent-row');
                        if (!parentRow) return;

                        const clickedLink = event.target.closest('a');

                        if (clickedLink && !clickedLink.classList.contains('toggle-children')) {
                            return;
                        }
                        event.preventDefault();

                        const parentId = parentRow.dataset.id;
                        const icon = parentRow.querySelector('.toggle-children i');

                        const isCollapsing = icon.classList.contains('icon-chevron-up');

                        icon.classList.toggle('icon-chevron-up');
                        icon.classList.toggle('icon-chevron-down');

                        toggleAllDescendants(parentId, isCollapsing);
                    });
                }

                function toggleAllDescendants(parentId, hide) {
                    const children = document.querySelectorAll(`tr[data-parent-id="${parentId}"]`);
                    children.forEach(child => {
                        child.classList.toggle('hidden-row', hide);

                        if (child.classList.contains('parent-row')) {
                            const childId = child.dataset.id;

                            if (hide) {
                                const icon = child.querySelector('.toggle-children i');
                                if (icon) {
                                    icon.classList.remove('icon-chevron-up');
                                    icon.classList.add('icon-chevron-down');
                                }
                                toggleAllDescendants(childId, true);
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-core::layouts.main>
