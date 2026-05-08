@props([
    'menu' => [],
])

@php
    $client = request()->client;
    $user = \Illuminate\Support\Facades\Auth::user();
@endphp

<header class="header">
    <div class="container-header">
        <div class="header__left">
            <div class="header__group">
                <a href="{{ Page::homeURL() }}" class="header__identity">
                    <span class="header__identity-logo">
                        <img class="header__logo" src="{{ Page::quantumAsset('images/logo-kampus.png') }}"
                             alt="Logo">
                    </span>
                    <h1 class="header__identity-title">
                        <span class="header__title">
                            {{ $client['name'] ?? config('app.name') }}
                        </span>
                        <span class="header__subtitle">
                            Penerimaan Mahasiswa Baru
                        </span>
                    </h1>
                </a>
            </div>
        </div>

        <div class="header__navigation">
            <x-core::layouts.outer.header.menu :data="$menu" />
        </div>

        <div class="header__right">
            @guest
                <div class="mobile">
                    <div class="header__action mobile">
                        <div class="dropdown dropdown_nav">
                            <a class="nav__link" href="#" data-toggle="dropdown">
                                <span class="icon icon-language-mini"></span>
                                ID
                            </a>
                            <div class="dropdown__box">
                                <div class="dropdown__box-header">
                                    <span class="icon icon-x-mark-mini" role="button" data-toggle="dropdown"></span>
                                    <span class="dropdown__box-title">Bahasa</span>
                                </div>
                                <ul class="dropdown__list">
                                    <li class="dropdown__item">
                                        <a href="#">ID - Indonesia</a>
                                    </li>
                                    <li class="dropdown__item">
                                        <a href="#">EN - English</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <x-core::button href="{{ route('admission.login') }}" class="btn-signin">
                        Masuk
                    </x-core::button>
                </div>
            @endguest

            <div class="header__navigation" style="align-items:center">
                @auth
                    <x-admission::header.profile :$user />
                @endauth
            </div>
        </div>
    </div>
</header>
