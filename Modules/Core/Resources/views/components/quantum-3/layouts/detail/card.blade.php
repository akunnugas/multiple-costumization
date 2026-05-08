@props([
    'action' => null,
    'data' => [],
    'subtitle' => null,
    'title' => null,
    'icon' => null,
    'pageConf' => [],
])
@php
    use Modules\Core\Helpers\Cstr;

    if (count($data) >= 8) {
        $half = ceil(count($data) / 2);
        $dataChunks = array_chunk($data, $half);
    }
@endphp
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header d-flex gap-2 align-items-center justify-content-between bg-white border-light-subtle p-3 rounded-top-4 border-2">
        <div class="d-flex gap-3 align-items-center">
            <div class="ratio ratio-1x1" style="width: 42px; min-width: 42px;">
                <span class="d-flex align-items-center justify-content-center rounded-circle p-2 border">
                    <i class="sym sym-{{ $icon ?? 'box' }}"></i>
                </span>
            </div>
            <div class="d-block ms-1">
                <h5 class="m-0 text-wrap truncate-2">
                    {{ Cstr::unescapeDeep($title) }}
                </h5>
                <span class="fs-6 text-secondary">{{ $subtitle }}</span>
            </div>
        </div>
        <div class="d-flex flex-shrink-0 gap-3">
            {{ $action }}
        </div>
    </div>
    <div class="card-body">
        <div class="row gy-3">
            @if ($slot->isEmpty())
                @if (!empty($dataChunks))
                    @foreach ($dataChunks as $card)
                        <div class="col-md-6">
                            <div class="row gx-1 gy-3">
                                @if (!isset($pageConf['custom_page']) || empty($pageConf['custom_page']))
                                    <x-core::quantum-3.layouts.detail.line :data="$card" />
                                @else
                                    @php
                                        $customPage = $pageConf['custom_page'];
                                        $customPageData = $pageConf['custom_page_data'] ?? [];
                                    @endphp
                                    <x-core::layouts.detail.line :data="$card" :$customPage :$customPageData />
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                <div class="col-md-6">
                    <div class="row gx-1 gy-3">
                        @if (!isset($pageConf['custom_page']) || empty($pageConf['custom_page']))
                            <x-core::quantum-3.layouts.detail.line :$data />
                        @else
                            @php
                                $customPage = $pageConf['custom_page'];
                                $customPageData = $pageConf['custom_page_data'] ?? [];
                            @endphp
                            <x-core::layouts.detail.line :$data :$customPage :$customPageData />
                        @endif
                    </div>
                </div>
                @endif
            @else
                {{ $slot }}
            @endif
            
        </div>
    </div>
</div>