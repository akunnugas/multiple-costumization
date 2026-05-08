@props([
    'title' => null,
    'isReport' => false,
])
<!DOCTYPE html>
<html lang="en">
    <style>
        table tr .cell-action .btn {
            max-width: none !important;
        }
    </style>

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
        @stack('headVendor')
        <link rel="stylesheet"
            href="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/styles/choices.min.css') }}">
        <script src="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/scripts/choices.min.js') }}"></script>
        <link href="{{ Page::quantumAsset('release/qn-202409120001.css') }}" rel="stylesheet">
        <link href="{{ asset('css/fix-grid.css') }}" rel="stylesheet">
        @vite('resources/scss/layouts/_override_main.scss')
        @stack('head')
    </head>

    <body {{ $attributes }}>
        {{ $slot }}
        @stack('end')
        {{-- <x-core::debug /> --}}
        @stack('scriptsVendor')
        @vite('resources/js/app.js')
        <script type="text/javascript" src="{{ Page::quantumAsset('release/qn-202409120001.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/validation.js') }}"></script>
        @stack('scriptsModule')
        @stack('scripts')

        <x-core::navigation-service.core />
    </body>
</html>
