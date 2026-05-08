@props([
    'action' => null,
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'class' => null,
    'withHeader' => true,
    'fullWidth' => true
])

<div @class([
    'container' => !$fullWidth,
    'container-fluid' => $fullWidth,
])>
    <div class="{{ empty($class) ? 'row row-cols-1 gy-3 p-3 p-lg-4' : $class }}">
        @if ($withHeader)
            <x-core::quantum-3.layouts.main.header :$action :$menu :$title :$subtitle />            
        @endif
        {{ $slot }}
    </div>
</div>
