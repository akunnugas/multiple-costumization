@props([
    'footer' => null,
    'header' => null,
    'title' => null,
    'navTab' => null
])
@php

@endphp
<div {{ $attributes->class('box-table') }}>
    @if(!empty($navTab))
        <div class="box-table__header header_tab">
            <nav class="nav-tab">
                <ul class="nav-tab__wrapper">
                    @php
                        $isActive = false;
                        $targetActive = null;
                    @endphp
                    @foreach($navTab as $item)
                        @php
                            $item['target'] = $item['target'] ?? \Illuminate\Support\Str::slug($item['path']);
                            $isActive = !empty($item['active']) ? 'active' : null;
                            $targetActive = $isActive ? $item['target'] : $targetActive;
                        @endphp
                        <a href="{{ url($item['path']) }}">
                            <li class="nav-tab__item {{ $isActive }}" data-toggle="tab" data-target="#{{ $item['target'] }}">
                                {{ $item['label'] }}
                            </li>
                        </a>
                    @endforeach
                </ul>
            </nav>
        </div>

        <div class="tab-content">
            <div class="tab-pane" id="{{ $targetActive }}">
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
            </div>
        </div>
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
