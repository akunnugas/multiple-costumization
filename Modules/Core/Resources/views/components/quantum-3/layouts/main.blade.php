@props([
    'action' => null,
    'menu' => [],
    'sidebar' => null,
    'subtitle' => null,
    'title' => null,
    'withContainer' => true,
    'fullWidth' => false
])
<x-core::quantum-3.layouts.main-outer :$menu :submenu="$sidebar" :$title>
    @if (!empty($sidebar))        
        <x-slot:sidebar>
            {{ $sidebar }}
        </x-slot:sidebar>
    @endif
    @if ($withContainer)
        <x-core::quantum-3.layouts.main.container :$menu :$action :$title :$subtitle :$fullWidth>
            {{ $slot }}
        </x-core::quantum-3.layouts.main.container>
    @else
        {{ $slot }}
    @endif
</x-core::quantum-3.layouts.main-outer>
