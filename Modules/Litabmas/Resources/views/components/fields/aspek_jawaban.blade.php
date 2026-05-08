@php
    $arr = explode('/', $value);
@endphp

@foreach ($arr as $item)
    {{ $loop->iteration }}. {{ $item }}<br>
@endforeach
