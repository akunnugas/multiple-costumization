@props([
    'data' => [],
    'subtitle' => null,
    'title' => null,
    'icon' => 'box-solid',
    'showCollapseInSection' => false,
    'sectionId' => ""
])

<div id="{{ $sectionId }}" class="card shadow-sm border-0 rounded-4">
    @if (!empty($title))            
        <div class="card-header bg-white border-light-subtle rounded-top-4 border-2">
            <div class="d-flex gap-2 align-items-center">
                <div class="ratio ratio-1x1" style="width: 2rem; min-width: 2rem;">
                    <span class="d-flex align-items-center justify-content-center rounded-circle p-2 border">
                        @if (!empty($icon))
                            <x-core::quantum-3.icon icon="{{ $icon }}" />
                        @endif
                    </span>
                </div>
                <h5 class="mb-0">{{ $title }}</h5>
            </div>
        </div>
    @endif
    <div class="card-body">
        @if (isset($alertStatic) && !empty($alertStatic))
            <div class="alert alert-{{ $alertStatic['type'] ?? 'info' }}" role="alert">
                <h4 class="alert-heading">{{ $alertStatic['title'] }}</h4>
                <p>{!! $alertStatic['message'] !!}</p>
            </div>
            <hr>
        @endif

        @if (!empty($collapseId))
            <div class="collapse show" id="collapse-{{ $collapseId }}">
        @endif
            <div class="row row-cols-1 row-cols-md-3 g-3">
                @if ($slot->isEmpty())
                    @foreach ($data as $item)
                        @php
                            $item['name'] ??= $item['field'] ?? '';
                            unset($item['field']);

                            $attributes = Page::buildAttributes($item);
                        @endphp
                        @if (!empty($item['separator']))
                            <div class="form-group col-md-12 d-flex align-items-center">
                                <hr class="w-100">
                                @if (!empty($item['label']))
                                    <span class="text-nowrap px-3">{{ $item['label'] }}</span>
                                    <hr class="w-100">
                                @endif
                            </div>
                        @else                            
                            <x-core::quantum-3.controls.form {{ $attributes }} />
                        @endif
                    @endforeach
                @else
                    {{ $slot }}
                @endif
            </div>
        @if (!empty($collapseId))
            </div>
        @endif
    </div>
</div>
