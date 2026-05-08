@props([
    'action' => null,
    'menu' => [],
    'subtitle' => null,
    'title' => null,
])
<div class="main__header">
    <div class="main__location">
        <x-core::layouts.main.header.breadcrumb :title="$subtitle ?? $title" />
        <div class="main__wrapper">
            <h1 class="main__title">{{ $title }}</h1>
            <p class="main__subtitle">{{ $subtitle }}</p>
        </div>
    </div>
    @if (!empty($action))
        <div class="main__action">
            {!! $action !!}
        </div>
    @endif
</div>
