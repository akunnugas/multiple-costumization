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
@endphp

<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu"/>
    </x-slot:sidebar>
    <x-slot:action>
        <x-core::button href="#" leading-icon="printer-solid"
                        onclick="printFromDivCLASS('.main .card.card_details-default')"/>
    </x-slot:action>

    <x-litabmas::layouts.detail.summary-cards :$data />

    @pushonce('head')
        <style>
            .col-12.col-sm-8.col-md-9.col-lg-9 {
                display: flex;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .card .row-data__value {
                width: unset;
            }

            @media (max-width: 768px) {
                .card .row-data__colon {
                    display: none;
                }
            }

            @media (min-width: 768px) {
                .card .row-data__colon {
                    display: inline-flex !important;
                }
            }
        </style>
    @endpushonce

    @pushonce('scripts')
        <script>
            function printFromDivCLASS(className) {
                window.print();
            }
        </script>
    @endpushonce
</x-core::layouts.main>
