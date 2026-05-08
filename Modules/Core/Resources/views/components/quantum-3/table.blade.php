@props([
    'footer' => null,
    'header' => null,
    'title' => null,
    'navTab' => null
])

<div {{ $attributes->class('box-table') }}>
    @if(!empty($navTab))
        {{-- #TODO: Quantum-3 Belum disesuaikan --}}
    @else
        @if (!empty($header))
            <div class="box-table__header">
                {{ $header }}
            </div>
        @endif
        <div class="box-table__content">
            @if (!empty($title))
                <h5 style="padding-bottom:0.375rem">{{ $title }}</h5>
            @endif
            {{ $slot }}
        </div>
        <div class="box-table__footer">
            @if (!empty($footer))
                {{ $footer }}
            @endif
        </div>
    @endif
</div>
