@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'action' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $data[0]['subtitle'] = null;
    $data[0]['edit_url'] = null;
@endphp
<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu" />
    </x-slot:sidebar>
    <x-core::layouts.html.alert />
    <x-litabmas::layouts.detail.cards :$data/>

    <x-litabmas::layouts.detail.card title="Proposal yang mengajukan pendanaan berdasarkan klaster">
        <x-slot:action>
            <div class="box-table__header">
                <div class="col-3" style="display: flex; gap: 10px;">
                    @foreach ($daftarProposal['filter'] as $key => $item)
                        @if (empty($item['options']))
                            @continue
                        @endif

                        @php
                            if ($item['hideLabel'] ?? false) {
                                $label = null;
                            } else {
                                $label = $item['label'] ?? \Modules\Core\Helpers\Page::defineTitleByResource($key);
                                $label = "Pilih {$label}";
                            }
                        @endphp
                        <x-core::select name="{{ $key }}" label="{{ $label }}" :is_empty="$item['is_empty'] ?? false"
                                        :options="$item['options']" :selected="$item['selected']"
                                        wire:change="setFilter('{{ $key }}', event.target.value)" />
                    @endforeach
                </div>
            </div>
        </x-slot:action>
        <div class="col-12">
            <x-core::table>
                @php
                    $headerDaftarProposal = $daftarProposal['header'];
                    $dataDaftarProposal = $daftarProposal['data']->items;
                    $showDetail = request()->permission['get'];
                @endphp
                <x-core::table.data :header="$headerDaftarProposal" :data="$dataDaftarProposal"
                                    :paginateInfo="$daftarProposal['data']" :sortable="true"
                                    :$showDetail :$showNumber
                />
                @if (empty($dataDaftarProposal))
                    @php
                        $handlerTitle = "Belum Ada Data Proposal";
                        $handleSubtitle = "Data proposal yang sudah lolos pendanaan akan muncul disini.";
                    @endphp
                    <x-core::handler :canCreate="false" :title="$handlerTitle" :subtitle="$handleSubtitle" />
                @endif
            </x-core::table>
        </div>
    </x-litabmas::layouts.detail.card>

    @pushonce('head')
        <style>
            .box-table__content {
                padding: 0 !important;
                border-top: none !important;
            }
            .card__header-right > .box-table__header {
                padding: unset;
            }
            .card__header-right .choices__list--single {
                padding-right: 1.5rem;
            }
        </style>
    @endpushonce

    @pushOnce('scripts')
        <script type="module">
            List.eventFilter("{!! Page::buildURL(['filter' => '__', 'page' => null]) !!}");
        </script>
    @endPushOnce
</x-core::layouts.main>
