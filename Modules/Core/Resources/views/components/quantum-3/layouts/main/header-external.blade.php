@props([
    'action' => null,
    'menu' => [],
    'subtitle' => null,
    'title' => null,
])

<div class="d-flex align-items-center justify-content-between gap-1 px-0 mb-2">
    <div class="d-flex flex-column gap-2">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-end gap-2">
            <h4 class="m-0">{{ $title }}</h4>
             {{-- <p class="text-secondary m-0">{{ $subtitle }}</p> --}}
        </div>
    </div>

    @if (!empty($action))
        <div class="d-flex gap-2">
            {!! $action !!}
        </div>
    @endif
</div>
