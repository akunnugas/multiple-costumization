@props([
    'title' => null,
    'parentNav' => [],
])
@php
    $home = Page::homeURL();
@endphp
<div class="breadcrumb-container">
    <div class="container">
        <ul class="breadcrumb">
            <li class="breadcrumb__item">
                <a href="{{ $home }}">
                    <span class="icon icon-home-solid"></span>
                    Beranda
                </a>
            </li>
            @foreach ($parentNav as $i => $item)
                <li class="breadcrumb__item">
                    <a href="{{ url('admission/' . $item['path']) }}">
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
            <li class="breadcrumb__item active">{{ $title }}</li>
        </ul>
    </div>
</div>
