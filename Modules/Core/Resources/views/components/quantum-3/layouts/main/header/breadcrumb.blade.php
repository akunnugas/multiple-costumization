@props([
    'title' => null,
])

<nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
    <ol class="breadcrumb mb-1">

        @isset($breadcrumb['home'])
            <li class="breadcrumb-item" aria-label="Home">
                <a href="{{ $breadcrumb['home'] }}"> <i class="sym sym-home-line"></i> </a>
            </li>
        @endisset

        @foreach ($breadcrumb['items'] as $i => $item)
            @if ($item['showLink'])
                <li class="breadcrumb-item"><a href="{{ url($item['path']) }}">{{ $item['label'] }}</a></li>
            @elseif (!empty($item['label']))
                <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
            @endif
        @endforeach

        @if ($breadcrumb['showTitle'])
            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
        @endif
    </ol>
</nav>
