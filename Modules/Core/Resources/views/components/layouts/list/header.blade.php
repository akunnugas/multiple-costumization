@props([
    'filter' => [],
    'search' => null,
    'title' => null,
])
<div class="grid">
    <div class="col-12 col-sm-4 col-md-3">
        <div class="form-control">
            <div class="form-control__group">
                <span data-input-icon="search"></span>
                <x-core::input type="search" value="{{ $search ?? request()->get('search') }}"
                    {{-- placeholder="Cari data{{ $title ? ' ' . $title : '' }}..." --}}
                    placeholder="Cari data ..."
                    wire:model.live.debounce.500ms="search" wire:click="resetSearch" />
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-8 col-md-9">
        <x-core::layouts.list.filter :data="$filter" />
    </div>
</div>
@if (empty($isLivewire))
    @pushOnce('scripts')
        <script type="module">
            List.eventSearch(
                "{!! Page::buildURL(['search' => '__', 'page' => null, 'create' => null, 'edit' => null]) !!}",
                "{!! Page::buildURL(['search' => null, 'page' => null, 'create' => null, 'edit' => null]) !!}"
            );
        </script>
    @endPushOnce
@endif
