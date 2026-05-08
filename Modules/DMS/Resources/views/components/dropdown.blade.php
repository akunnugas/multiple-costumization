@props([
    'items' => [],
    'position' => 'left'
])

@php
    use Illuminate\View\ComponentAttributeBag;
@endphp

@pushOnce('head')
    @vite('Modules/DMS/Resources/assets/sass/components/dropdown.scss')
@endPushOnce

<div class="dropdown dropdown-{{ $position }}">
    {{ $slot }}

    <div class="dropdown__box">
        <ul class="dropdown__list">
            @foreach ($items as $item)
                <li class="dropdown__item">
                    <a
                        @if(isset($item['href'])) href="{{ $item['href'] }}" @endif
                        {{ new ComponentAttributeBag($item['attributes'] ?? []) }}
                    >
                        @if ($item['icon'])
                            <x-core::icon type="{{ $item['icon'] }}" />
                        @endif
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
