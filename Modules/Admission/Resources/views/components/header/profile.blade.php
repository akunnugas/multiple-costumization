@props([
    'user'
])

<div class="dropdown dropdown_nav dropdown_profile">
    <button class="header__user" data-toggle="dropdown">
        <span class="header__user-wrapper">
            <span class="header__user-avatar">
                <span class="avatar avatar_sm">
                    <img src="{{ Page::quantumAsset('images/example-profile.jpg') }}"
                         alt="User Avatar" width="44">
                </span>
            </span>
            <span class="header__user-info">
                <span class="header__user-name">{{ $user->name }}</span>
                <span class="header__user-role">{{ $user->role_name }}</span>
            </span>
        </span>
        <span class="icon icon-chevron-down-solid"></span>
    </button>
    <div class="dropdown__box dropdown__box_menu-end">
        <ul class="dropdown__list">
            <li class="dropdown__avatar">
                <div class="avatar avatar_sm">
                    <div class="avatar__circle">
                        <img src="{{ Page::quantumAsset('images/example-profile.jpg') }}"
                             alt="Avatar Image">
                    </div>
                    <div class="avatar__text">
                        <span class="avatar__title">{{ $user->name }}</span>
                        <span class="avatar__subtitle">{{ $user->role_name }}</span>
                    </div>
                </div>
            </li>
            <li class="dropdown__line"></li>
            <li class="dropdown__item">
                <a href="#">
                    <span class="icon icon-language-mini"></span>
                    ID - Indonesia
                </a>
            </li>
            <li class="dropdown__item" data-click-for="#qn-nav-help">
                <a href="#">
                    <span class="icon icon-question-mark-circle-mini"></span>
                    Bantuan
                </a>
            </li>
            <li class="dropdown__item dropdown__item_danger">
                <a href="{{ route('admission.logout') }}">
                    <span class="icon icon-arrow-left-on-rectangle-mini"></span>
                    Keluar
                </a>
            </li>
        </ul>
    </div>
</div>
