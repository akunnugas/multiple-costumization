@props([
    'title' => null,
    'isReport' => false,
])
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>
            @if ($isReport)
                {{ $title }}
            @else
                {{ config('app.name') . (empty($title) ? '' : ' - ' . $title) }}
            @endif
        </title>
        <link rel="icon" href="{{ asset('images/icon-sevima-platform.png') }}">
        @vite([
            'Modules/Core/Resources/assets/quantum-3/scss/app.scss',
            'Modules/Core/Resources/assets/quantum-3/js/app.js'
        ])
        
        @stack('headVendor')

        @stack('head')
    </head>
    <style>
        .qn-header-pattern {
            background-image: url("{{ url('images/patterns/sevima-header.webp') }}");
        }

        .qn-sidebar {
            background-image: url("{{ url('images/patterns/sevima-sidebar.svg') }}");
        }

        @font-face {
            font-display: block;
            font-family: "quantum-symbols";
            src: url("{{ asset('fonts/quantum-symbols.woff2') }}") format("woff2"),
            url("{{ asset('fonts/quantum-symbols.woff') }}") format("woff");
        }
    </style>
    <body>
        {{ $slot }}
        @stack('end')
        {{-- <x-core::debug /> --}}
        @stack('scriptsVendor')

        @stack('scriptsModule')
        @stack('scripts')

        {{-- <x-core::navigation-service.core /> --}}
        @livewireScripts
    </body>

    @php
        $authUser = auth()->user();
    @endphp
    @if (config('app.env') !== 'local' && !$authUser->is_internal)
        @php
            $isLiveChatEnabled = in_array($authUser->kode_role, [
                Modules\Gate\Models\Role::ROLE_ADMINPT,
                Modules\Gate\Models\Role::ROLE_ADMIN_PENJAMINAN_MUTU,
                Modules\Gate\Models\Role::ROLE_LITABMAS_ADMIN_LPPM,
                Modules\Gate\Models\Role::ROLE_ADMIN_KERJASAMA
            ]);
        @endphp

        @if ($isLiveChatEnabled)
            @include('core::components.layouts.livechat')
        @endif
    @endif
</html>
