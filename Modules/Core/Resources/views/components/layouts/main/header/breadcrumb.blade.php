@props([
    'title' => null,
])
<ul class="breadcrumb">
    @isset($breadcrumb['home'])
        <li class="breadcrumb__item">
            <a href="{{ $breadcrumb['home'] }}">
                <span class="icon icon-home-solid"></span>
            </a>
        </li>
    @endisset
    @foreach ($breadcrumb['items'] as $i => $item)
        @if ($item['showLink'])
            <li class="breadcrumb__item">
                <a href="{{ url($item['path']) }}">
                    {{ $item['label'] }}
                </a>
            </li>
        @elseif (!empty($item['label']))
            <li class="breadcrumb__item active">{{ $item['label'] }}</li>
        @endif
    @endforeach
    @if ($breadcrumb['showTitle'])
        <li class="breadcrumb__item active">{{ $title }}</li>
    @endif
</ul>
