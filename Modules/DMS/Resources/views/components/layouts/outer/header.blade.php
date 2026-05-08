@props([
    'menu' => [],
])
@php
    $client = request()->client;
    $user = Auth::user();
@endphp

@pushOnce('head')
    @vite('Modules/DMS/Resources/assets/sass/components/header.scss')
@endPushOnce

<header {{ $attributes->merge(['class' => 'header']) }}>
    <div class="header__left" style="flex-shrink: 0">
        <div class="header__group">
            <a href="{{ Page::homeURL() }}" class="header__identity">
                <span class="header__identity-logo">
                    <img class="header__logo" src="{{ Page::quantumAsset('images/logo-kampus.png') }}" alt="Logo">
                </span>
                <h1 class="header__identity-title">
                    <span class="header__title">{{ $client['nama_klien'] ?? config('app.name') }}</span>
                </h1>
            </a>
        </div>
    </div>

    <div class="header__search">
        <livewire:dms::search-bar :folderId="$folderId ?? null" />
    </div>

    <div class="header__right">
        <div class="header__action">
            <a href="#" class="btn btn_icon btn_nav" data-toggle="dropdown" id="qn-nav-help">
                <span class="icon icon-question-mark-circle"></span>
            </a>
            <div class="dropdown dropdown_nav dropdown_notification">
                <button class="btn btn_icon btn_nav" data-toggle="dropdown" id="qn-nav-notification">
                    <span class="icon icon-bell"></span>
                </button>
                <div class="dropdown__box dropdown__box_menu-end">
                    <div class="notification">
                        <div class="notification__header">
                            <div class="notification__wrapper">
                                <h3 class="notification__title">
                                    Notification
                                </h3>
                                <a href="#" class="notification__header-btn">
                                    Mark all as read
                                </a>
                            </div>
                            <nav class="nav-tab">
                                <ul class="nav-tab__wrapper">
                                    <li class="nav-tab__item active" data-toggle="tab"
                                        data-target="#notification-general-section">
                                        General
                                    </li>
                                    <li class="nav-tab__item" data-toggle="tab"
                                        data-target="#notification-following-section">
                                        Following
                                    </li>
                                    <li class="nav-tab__item" data-toggle="tab"
                                        data-target="#notification-archive-section">
                                        Archive
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div class="notification__body">
                            <div class="notification__section" id="notification-general-section">
                                <div class="notification__item">
                                    <h3 class="notification__item-title">TODAY</h3>
                                    <div class="notification__item-body">
                                        <div class="notification__avatar">
                                            <div class="header__user">
                                                <div class="header__user-avatar">
                                                    <div class="avatar avatar_sm">
                                                        <img src="{{ Page::quantumAsset('images/example-profile.jpg') }}"
                                                            alt="User Avatar" width="44">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="notification__content">
                                            <a href="#" class="notification__content-nav">
                                                <span class="notification__text">
                                                    <b>Joy Pacheco</b> mentioned you on <b>Improve card readas</b>
                                                </span>
                                                <span class="notification__text-muted">
                                                    2h ago - Engineering
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification__item">
                                    <h3 class="notification__item-title">TODAY</h3>
                                    <div class="notification__item-body">
                                        <div class="notification__avatar">
                                            <div class="header__user">
                                                <div class="header__user-avatar">
                                                    <div class="avatar avatar_sm">
                                                        <img src="{{ Page::quantumAsset('images/example-profile.jpg') }}"
                                                            alt="User Avatar" width="44">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="notification__content">
                                            <a href="#" class="notification__content-nav">
                                                <span class="notification__text">
                                                    <b>Joy Pacheco</b> mentioned you on <b>Improve card readas</b>
                                                </span>
                                                <span class="notification__text-muted">
                                                    2h ago - Engineering
                                                </span>
                                            </a>
                                            <p class="notification__desc">
                                                I’ve strated working on a first draft, feel free to take a look and tell
                                                me what you think
                                            </p>
                                            <a href="/index.html" download="New File Download.html"
                                                class="notification__box">
                                                <img src="{{ Page::quantumAsset('images/misc-icons/file-Dokumen/docx-solid.svg') }}"
                                                    alt="Docs" class="notifiaction__box-file">
                                                <span class="notification__box-title">
                                                    Prototype recap.doc
                                                </span>
                                                <span class="notification__box-dot"></span>
                                                <span class="notification__box-info">
                                                    2MB
                                                </span>
                                            </a>
                                            <a href="/index.html" download="New File Download.html"
                                                class="notification__box">
                                                <img src="{{ Page::quantumAsset('images/misc-icons/file-Dokumen/docx-solid.svg') }}"
                                                    alt="Docs" class="notifiaction__box-file">
                                                <span class="notification__box-title">
                                                    Prototype recap.doc
                                                </span>
                                                <span class="notification__box-dot"></span>
                                                <span class="notification__box-info">
                                                    2MB
                                                </span>
                                            </a>
                                            <a href="#" class="notification__more">
                                                +3 more file
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification__item">
                                    <h3 class="notification__item-title">TODAY</h3>
                                    <div class="notification__item-body">
                                        <div class="notification__avatar">
                                            <div class="header__user">
                                                <div class="header__user-avatar">
                                                    <div class="avatar avatar_sm">
                                                        <img src="{{ Page::quantumAsset('images/example-profile.jpg') }}"
                                                            alt="User Avatar" width="44">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="notification__content">
                                            <a href="#" class="notification__content-nav">
                                                <span class="notification__text">
                                                    <b>Joy Pacheco</b> mentioned you on <b>Improve card readas</b>
                                                </span>
                                                <span class="notification__text-muted">
                                                    2h ago - Engineering
                                                </span>
                                            </a>
                                            <div class="notification__btn-group">
                                                <button type="button" class="btn btn_outline">Button
                                                    Secondary</button>
                                                <button type="button" class="btn btn_primary">Button Primary</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="notification__section" id="notification-following-section">
                                ...
                            </div>
                            <div class="notification__section" id="notification-archive-section">
                                ...
                            </div>
                        </div>
                        <div class="notification__footer">
                            <a href="#" class="notification__footer-btn">
                                View all notification
                            </a>
                            <a href="#" class="notification__footer-setting">
                                <span class="icon icon-cog-6-tooth"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dropdown dropdown_nav dropdown_module">
                <button class="btn btn_outline btn_nav btn_module" data-toggle="dropdown">
                    <span class="icon icon-squares-2x2"></span>
                    <span class="btn__text">{{ $user->nama_modul }}</span>
                </button>
                <div class="dropdown__box dropdown__box_menu-end" data-dropdown-title="Modul">
                    <ul class="module">
                        @foreach ($user->modul as $modul)
                            <li class="module__item">
                                <a href="{{ $modul['url_home'] }}" class="module__btn">
                                    <span class="module__box">
                                        <img src="https://www.colorhexa.com/cdd5df.png" class="module__image"
                                            alt="Setting Logo">
                                    </span>
                                    <span class="module__title">{{ $modul['nama_modul'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="dropdown dropdown_nav dropdown_profile">
            <button class="header__user" data-toggle="dropdown">
                <span class="header__user-wrapper">
                    <span class="header__user-avatar">
                        <span class="avatar avatar_sm">
                            <img src="{{ Page::quantumAsset('images/example-profile.jpg') }}" alt="User Avatar"
                                width="44">
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
                                <img src="{{ Page::quantumAsset('images/example-profile.jpg') }}" alt="Avatar Image">
                            </div>
                            <div class="avatar__text">
                                <span class="avatar__title">{{ $user->nama_user }}</span>
                                <span class="avatar__subtitle">{{ $user->nama_role }}</span>
                            </div>
                        </div>
                        <div class="progress">
                            <label class="progress__label">Penyimpanan</label>
                            <div class="progress__bar" data-percent-start="0" data-percent-end="60"></div>
                        </div>
                    </li>
                    <li class="dropdown__line"></li>
                    <li class="dropdown__item">
                        <a href="<?php echo url(\App\Providers\RouteServiceProvider::HOME); ?>">
                            Switch Role
                            <span class="dropdown__item-right">
                                <span class="icon icon-arrow-path-rounded-square-mini"></span>
                            </span>
                        </a>
                    </li>
                    <li class="dropdown__item dropdown__item_sm-hidden" data-click-for="#qn-nav-notification">
                        <a href="#">Notification</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="#">Manage Account</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="#">Media Library</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="#">Upgrade PRO</a>
                    </li>
                    <li class="dropdown__item">
                        <a href="#">Pengaturan</a>
                    </li>
                    <li class="dropdown__line"></li>
                    <li class="dropdown__item">
                        <a href="#">
                            <span class="icon icon-language-mini"></span>
                            Bahasa Indonesia
                        </a>
                    </li>
                    <li class="dropdown__item" data-click-for="#qn-nav-help">
                        <a href="#">
                            <span class="icon icon-question-mark-circle-mini"></span>
                            Bantuan
                        </a>
                    </li>
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
