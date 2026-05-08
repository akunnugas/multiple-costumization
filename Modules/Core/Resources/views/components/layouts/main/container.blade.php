@props([
    'action' => null,
    'menu' => [],
    'subtitle' => null,
    'title' => null,
])
<div class="container">
    <x-core::layouts.main.header :$action :$menu :$title :$subtitle />
    {{ $slot }}
</div>
