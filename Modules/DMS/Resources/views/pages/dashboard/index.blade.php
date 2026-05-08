@php
    use Modules\DMS\Helpers\Menu;
@endphp

@pushOnce('head')
    @vite('Modules/DMS/Resources/assets/sass/dashboard.scss')
@endPushOnce

<x-dms::layouts.dashboard :title="__('dms::pages.sevima_platform')">
    <div class="card modules">
        <div class="card__body">
            @foreach(Menu::modules() as $module)
                <x-dms::module-card :$module />
            @endforeach
        </div>
    </div>
</x-dms::layouts.dashboard>

