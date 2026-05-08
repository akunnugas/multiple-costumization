@props([
    'action' => null,
    'data' => [],
    'subtitle' => null,
    'title' => null,
    'pageConf' => [],
    'variant' => 'default', // default, primary
    'customClassBody' => 'grid',
])
@pushonce('head')
    <style>
        .card__body .card__content label {
            font-size: 12px !important;
            color: #363636 !important;
        }
    </style>
@endpushonce
<div class="card card_details-{{ $variant }}">
    @if(!empty($title) || !empty($subtitle) || !empty($action))
        <div class="card__header">
            <div class="card__header-left">
                <div class="card__header-block">
                    @if($title)
                        <h2 class="header__title">{{ $title }}</h2>
                    @endif
                    @if($subtitle)
                        <span class="header__subtitle">{{ $subtitle }}</span>
                    @endif
                </div>
            </div>
            @if (!empty($action))
                <div class="card__header-right">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif
    <div class="card__body">
        <div class="{{ $customClassBody }}">
            @if ($slot->isEmpty())
                @if (!isset($pageConf['custom_page']) || empty($pageConf['custom_page']))
                    <x-litabmas::layouts.detail.line :$data />
                @else
                    @php
                        $customPage = $pageConf['custom_page'];
                        $customPageData = $pageConf['custom_page_data'] ?? [];
                    @endphp
                    <x-litabmas::layouts.detail.line :$data :$customPage :$customPageData />
                @endif
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</div>
