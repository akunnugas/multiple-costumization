@props([
    'data' => [],
    'subtitle' => null,
    'title' => null,
    'icon' => null,
    'showCollapseInSection' => false,
])
@pushOnce('head')
    <style>
        .btn_outline.btn_icon.btn_sm[aria-expanded="true"] .icon {
            transform: rotate(180deg);
        }
        .a-link {
            color: var(--qn-primary-400);
            text-decoration: underline;
        }
    </style>
@endPushOnce
<div class="col-12">
    @if (isset($alertStatic) && !empty($alertStatic))
        {{-- alert_helper, alert_warning, alert_danger --}}
        <div class="alert alert alert_{{ $alertStatic['type'] ?? 'helper' }}">
            <div class="alert__content">
                <h4 class="alert__heading">{{ $alertStatic['title'] }}</h4>
                <p>
                    {!! $alertStatic['message'] !!}
                </p>
            </div>
        </div>
        <br>
    @endif
    <div {{ $attributes->merge(['class' => 'card card_form', 'id' => Str::slug($title)]) }}>
        @if (!empty($title) && $showCollapseInSection)
            <div class="card__header">
                <div class="form-header">
                    <div class="form-header__wrapper">
                        <div class="form-header__avatar">
                            <span class="icon icon-{{ $icon ?? 'cube' }}-mini"></span>
                        </div>
                        <div class="form-header__information">
                            <h3 class="form-header__title">{{ $title }}</h3>
                            <p class="form-header__subtitle">{{ $subtitle }}</p>
                        </div>
                    </div>
                    @if($showCollapseInSection)
                        @php
                            $collapseId = Str::slug($title);
                        @endphp
                        <button type="button" class="btn btn_outline btn_icon btn_sm show" role="button"
                                data-toggle="collapse" aria-expanded="true" data-target="#collapse-{{ $collapseId }}">
                            <span class="icon icon-chevron-down-solid"></span>
                        </button>
                    @endif
                </div>
            </div>
        @endif
        @if(!empty($collapseId))
            <div class="collapse show" id="collapse-{{ $collapseId }}">
        @endif
        <div class="card__body">
            <div class="grid cols-1">
                @if ($slot->isEmpty())
                    @foreach ($data as $item)
                        @php
                            $item['name'] ??= $item['field'];
                            unset($item['field']);

                            $attributes = Page::buildAttributes($item);
                        @endphp
                        <x-core::controls.form {{ $attributes }} />
                    @endforeach
                @else
                    {{ $slot }}
                @endif
            </div>
        </div>
        @if(!empty($collapseId))
            </div>
        @endif
    </div>
</div>
