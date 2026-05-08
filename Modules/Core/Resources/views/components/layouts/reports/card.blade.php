@props([
    'data' => [],
    'subtitle' => null,
    'title' => null,
    'icon' => null,
    'showCollapseInSection' => false,
])
<div class="col-12 d-flex">
    <div {{ $attributes->merge(['class' => 'card card_form', 'id' => Str::kebab($title)]) }}>
        @if (!empty($title))
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
                            $collapseId = Str::kebab($title);
                        @endphp
                        <button type="button" class="btn btn_outline btn_icon btn_sm show" role="button"
                                data-toggle="collapse" data-target="#collapse-{{ $collapseId }}">
                            <span class="icon icon-chevron-down-solid"></span>
                        </button>
                    @endif
                </div>
            </div>
        @endif
        @if(!empty($collapseId))
            <div class="collapse" id="collapse-{{ $collapseId }}">
        @endif
        <div class="card__body">
            <div class="grid cols-1">
                @if ($slot->isEmpty())
                    @foreach ($data as $key => $item)
                        @php
                            $item['name'] ??= $key;
                            unset($key);

                            $attributes = Page::buildAttributes($item);
                        @endphp
                        <x-core::controls.form {{ $attributes }} />
                    @endforeach
                @else
                    {{ $slot }}
                @endif
            </div>

            <div class="footer-btn pull-right d-flex">
                <x-core::button type="button" id="goSubmitReport" variant="primary" leadingIcon="eye">
                    Tampilkan
                </x-core::button>
                <x-core::button type="button" id="goSubmitBlankReport" variant="primary" leadingIcon="arrow-top-right-on-square">
                    Lihat di Tab Baru
                </x-core::button>
            </div>
        </div>
        @if(!empty($collapseId))
            </div>
        @endif
    </div>
</div>
