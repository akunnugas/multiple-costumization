@props([
    'action' => null,
    'data' => [],
    'subtitle' => null,
    'title' => null,
    'icon' => null,
    'pageConf' => [],
])
<div class="card card_details-default" style="flex-grow: 1;">
    @if (!empty($action))
        <div class="card__header">
            <div class="card__header-left">
            </div>
                <div class="card__header-right">
                    {{ $action }}
                </div>
        </div>
    @endif
    <div class="card__body util_d-flex util_flex-center">
        <div class="@if(isset($pageConf['is_full_width']) && $pageConf['is_full_width']) util_w-100 util_px-40 @else util_w-60 @endif">
            <h2 class="card__content_title" style="font-size: 1rem !important">{{ $title }}</h2>
            <div class="card__content">
                @if ($slot->isEmpty())
                    @if (!isset($pageConf['custom_page']) || empty($pageConf['custom_page']))
                        <x-core::layouts.detail.line :$data />
                    @else
                        @php
                            $customPage = $pageConf['custom_page'];
                            $customPageData = $pageConf['custom_page_data'] ?? [];
                        @endphp
                        <x-core::layouts.detail.line :$data :$customPage :$customPageData />
                    @endif
                @else
                    {{ $slot }}
                @endif
            </div>
        </div>
    </div>
</div>
