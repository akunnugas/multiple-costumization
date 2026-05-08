@props([
    'menu' => [],
])
@php
    $client = request()->client;
    $user = Auth::user();

    if (!empty($user->kode_modul)) {
        $roles = $user->modul[$user->kode_modul]['role'];
    }
@endphp
<header {{ $attributes->merge(['class' => 'header']) }}>
    <div class="header__left">
        <div class="header__group">
            <a href="{{ Page::homeURL() }}" class="header__identity">
                <span class="header__identity-logo">
                    <img class="header__logo"
                        src="{{ session('token.logo_univ') ?? Page::quantumAsset('images/logo-kampus.png') }}"
                        alt="Logo">
                </span>
                <h1 class="header__identity-title" style="display:block">
                    <span class="header__title" style="font-weight: normal;font-size:12px;">SIM
                        {{ $user->nama_modul }}</span>
                    <span class="header__title">{{ $client['nama_klien'] ?? config('app.name') }}</span>
                </h1>
            </a>
        </div>
        <div class="header__navigation">
            <x-core::layouts.outer.header.menu :data="$menu" />
        </div>
    </div>
    <div class="header__right">
        <div class="header__action">
            <a href="{{ $urlMenuSiakad ?? '#' }}">
                <button class="btn btn_outline btn_nav btn_module">
                    <span class="icon icon-squares-2x2"></span>
                    <span class="btn__text">{{ $user->nama_modul }}</span>
                </button>
            </a>
            {{-- <div class="dropdown dropdown_nav dropdown_module">
                <button class="btn btn_outline btn_nav btn_module" data-toggle="dropdown">
                    <span class="icon icon-squares-2x2"></span>
                    <span class="btn__text">{{ $user->nama_modul }}</span>
                </button>
                <div class="dropdown__box dropdown__box_menu-end" data-dropdown-title="Modul">
                    <ul class="module">
                        @foreach ($user->modul as $module)
                        <li class="module__item">
                            <a href="{{ $module['url_home'] }}" class="module__btn">
                                <span class="module__box">
                                    <img src="https://www.colorhexa.com/cdd5df.png" class="module__image"
                                        alt="Setting Logo">
                                </span>
                                <span class="module__title">{{ $module['nama_modul'] }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div> --}}
        </div>
        <div class="dropdown dropdown_nav dropdown_profile">
            <button class="header__user" data-toggle="dropdown">
                <span class="header__user-wrapper">
                    <span class="header__user-avatar">
                        <span class="avatar avatar_sm">
                            <p class="avatar__acronym">
                                {{ substr($user->nama_user, 0, 2) }}
                            </p>
                        </span>
                    </span>
                    <span class="header__user-info">
                        <span class="header__user-name">{{ $user->nama_user }}</span>
                        <span class="header__user-role">{{ $user->nama_role }}</span>
                    </span>
                </span>
                <span class="icon icon-chevron-down-solid"></span>
            </button>
            <div class="dropdown__box dropdown__box_menu-end">
                <ul class="dropdown__list">
                    <li class="dropdown__avatar">
                        <div class="avatar avatar_sm">
                            <div class="avatar__circle">
                                <p class="avatar__acronym">
                                    {{ substr($user->nama_user, 0, 2) }}
                                </p>
                            </div>
                            <div class="avatar__text">
                                <span class="avatar__title">{{ $user->nama_user }}</span>
                                <span class="avatar__subtitle">{{ $user->nama_role }}</span>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown__line"></li>
                    <li class="dropdown__item">
                        <a href="#" id="switch-role">
                            Ganti Role
                            <span class="dropdown__item-right">
                                <span class="icon icon-arrow-path-rounded-square-mini"></span>
                            </span>
                        </a>
                    </li>
                    <li class="dropdown__line"></li>
                    <li class="dropdown__item dropdown__item_danger">
                        <a href="{{ url(route('logout')) }}">
                            <span class="icon icon-arrow-left-on-rectangle-mini"></span>
                            Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
@if (!empty($roles))
    <x-core::modal.switch-role id="modal-switch-role" :roles="$roles" :module="$user?->kode_modul" :selectedRole="$user?->kode_role"
        :selectedOrganization="$user?->id_unit" :action="route('switch-role')" />
@endif
@pushonce('head')
    <style>
        #modal-switch-role .box-switch {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 6px;
            cursor: pointer;
            font-size: 30px;
            border-radius: 10px;
        }

        #modal-switch-role .box-switch.selected:hover {
            background-color: transparent;
        }

        #modal-switch-role .box-switch.selected {
            cursor: default;
        }

        #modal-switch-role .box-switch:hover {
            background-color: #f5f5f5;
        }

        /* .modal {
                        padding-top: 0 !important;
                    } */


        .form-control__input {
            padding: 0.625rem 0.75rem;
        }

        .header {
            border-bottom: 1px solid #EEF2F6;
            box-shadow: 0px 2px 4px 0px rgba(27, 28, 29, 0.04);
        }

        .header__title {
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 20px;
        }

        .header__navigation .nav .nav__list .nav__item .nav__link,
        .header__navigation .nav .nav__list .nav__item.active .nav__link,
        .header__navigation .nav .nav__list .nav__item .nav__link.show {
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.25rem;
        }

        .header__navigation .nav .nav__list .nav__item .nav__link {
            color: #36383A;
        }

        .header__navigation .nav .nav__list .nav__item.active .nav__link {
            color: #0F6AF5;
        }

        .header__action .icon {
            color: #36383A;
        }

        .header__identity::after {
            content: "";
            height: 2rem;
            background-color: #CDD5DF;
        }

        .breacrumb__item {
            color: #074BB2;
            font-weight: 400;
            line-height: 1.375rem;
            font-kerning: none;
        }

        .breacrumb__item.active {
            color: #36383A;
        }

        .main__title {
            font-size: 1.25rem;
            font-weight: 600;
            line-height: 2rem;
        }
    </style>
@endpushonce
