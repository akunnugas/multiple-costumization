<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LITABMAS | Detail Pengajuan Pendanaan</title>
    <link rel="icon" href="{{ asset('images/icon-sevima-platform.png') }}">
    <link href="{{ Page::quantumAsset('release/qn-202407120001.css') }}" rel="stylesheet">
    <style>
        .col-12.col-sm-8.col-md-9.col-lg-9 {
            display: flex;
            align-items: flex-start;
            gap: 0.25rem;
        }
        .card .row-data__value {
            width: unset;
        }

        .card .row-data__value ol {
            padding-left: 0.75rem;
        }

        .col-block_blue {
            background-color: #0F6AF5;
            width: fit-content;
            color: #fff;
            padding: 2px;
        }
        .badge_default {
            --qn-badge-background: #FCFCFD;
            --qn-badge-border-color: var(--qn-neutral-400);
            --qn-badge-color: #575C62;
            --qn-badge-font-size: 0.75rem;
            --qn-badge-font-weight: 500;
            --qn-badge-line-height: 1.5rem;
        }
        .row-data__title {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.125rem;
            display: inline-flex;
            padding-right: 0.75rem;
        }
        .badge.badge_outline-warning.badge_sm {
            display: inline-flex;
        }
        .cell-inline {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar.sidebar_right {
            left: unset;
            right: 0;
            width: 264px;
            max-width: unset;
            border-right: none;
            padding: 24px 16px;

        }
        .sidebar.sidebar_right .sidebar__title {
            font-size: 1rem;
            font-weight: 600;
            line-height: 28px;
            padding-bottom: 12px;
        }
        .sidebar.sidebar_right + .main {
            padding-right: 264px;
        }

        @media (max-width: 768px) {
            .sidebar.sidebar_right {
                display: none;
            }
            .sidebar.sidebar_right + .main {
                padding-right: 0;
            }
            .card .row-data__colon {
                display: none;
            }
            .card .card__header .card__header-left {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn.btn_outline#btn-edit-desktop {
                display: none;
            }
        }

        @media only screen and (max-width: 79.937rem) {
            .sidebar+.main {
                padding-left: 0!important;
            }
        }

        @media (min-width: 768px) {
            .card .row-data__colon {
                display: inline-flex !important;
            }

            .btn.btn_outline#btn-edit-mobile {
                display: none;
            }
        }

        .stepper {
            display: flex;
            flex-direction: column;
        }

        .stepper .stepper__item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 2rem;
        }

        .stepper .stepper__item.stepper_agenda {
            display: grid;
            /* grid-template-rows: [text-row] auto [line-row] 20px; */
            grid-template-columns: [counter-column] 20px [text-column] auto;
            column-gap: 1rem;
            row-gap: 0.5rem;
        }

        .stepper .stepper__item .stepper__item-order {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 50%;
            border: 1px solid #9AA4B2;
        }

        .stepper .stepper__item .stepper__item-order-number {
            font-size: 12px;
            font-style: normal;
            font-weight: 600;
            line-height: 18px;
            color: #9AA4B2;
        }

        .stepper .stepper__item.stepper_complete .stepper__item-order {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.30);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.00) 0%, rgba(255, 255, 255, 0.00) 100%), #0F6AF5;
            box-shadow: 0px 0px 0px 1px rgba(0, 84, 211, 0.76), 0px 1px 2px 0px rgba(13, 43, 89, 0.40), 0px 0px 0px 3px rgba(48, 130, 255, 0.16);
        }

        .stepper .stepper__item.stepper_complete .stepper__item-order-number {
            color: #fff;
        }

        .stepper .stepper__item.stepper_active .stepper__item-order {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 50%;
            border: 1px solid #0F6AF5;
            background: #fff;
            box-shadow: 0px 0px 0px 3px rgba(48, 130, 255, 0.16);
        }

        .stepper .stepper__item.stepper_active .stepper__item-order-number {
            color: #0F6AF5;
        }

        .stepper .stepper__item .stepper__item-order::after {
            content: '';
            position: absolute;
            display: flex;
            align-items: center;
            width: 2px;
            min-height: 32px;
            height: 100%;
            top: 32px;
            background: #E3E8EF;
            border-radius: 2px;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-order::after {
            content: '';
            position: absolute;
            display: flex;
            align-items: center;
            width: 2px;
            min-height: 32px;
            height: 100%;
            top: 32px;
            background: #E3E8EF;
            border-radius: 2px;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-line {
            display: flex;
            justify-content: center;
            width: 2px;
            height: 100%;
            background: #E3E8EF;
            margin-left: 11px;
            margin-top: 50px;
        }

        .stepper .stepper__item.stepper_complete .stepper__item-order::after,
        .stepper .stepper__item.stepper_complete.stepper_agenda .stepper__item-line {
            background: #0F6AF5;
        }

        .stepper .stepper__item:last-child .stepper__item-order::after {
            display: none;
        }

        .stepper .stepper__item .stepper__item-title {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .stepper .stepper__item .stepper__item-title .stepper__item-name {
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25rem;
            color: #202939;
        }

        .stepper .stepper__item .stepper__item-title .stepper__item-date {
            font-size: 0.75rem;
            font-weight: 400;
            line-height: 1.125rem;
            color: #697586;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #E3E8EF;
            width: 196px;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda .stepper__item-agenda-title {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.125rem;
            color: #202939;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda .stepper__item-agenda-link {
            font-size: 0.75rem;
            font-weight: 400;
            line-height: 1.125rem;
            color: #9AA4B2;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-agenda .stepper__item-agenda-link:hover {
            text-decoration: underline;
        }

        .stepper .stepper__item.stepper_agenda .stepper__item-content .stepper__item-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            padding: 4px;
            border-radius: 50%;
            background: #F1F6FE;
            color: #0F6AF5;
            font-size: 1rem;
        }

        .box-table__content#table-docs {
            border-top: none;
        }
    </style>
</head>

<body>
    <!-- [START] (for all layouts) Header -->
    <header class="header">
        <div class="header__left">
            <div class="header__group">
                <a href="/index.html" class="header__identity">
                    <span class="header__identity-logo">
                        <img class="header__logo" src="{{ asset('images/icon-sevima-platform.png') }}" alt="Quantum Logo" width="40">
                    </span>
                    <h1 class="header__identity-title">
                        <span class="header__title">Universitas Sevima Nusantara Indonesia</span>
                    </h1>
                </a>
            </div>
            <div class="header__navigation">
                <nav class="nav">
                    <ul class="nav__list" data-more-text="Lainnya">
                        <li class="nav__item">
                            <a class="nav__link" href="#">
                                <span>Beranda</span>
                            </a>
                        </li>
                        <li class="nav__item active">
                            <a class="nav__link" href="#">
                                <span>Pengajuan Pendanaan</span>
                            </a>
                        </li>
                        <li class="nav__item">
                            <a class="nav__link" href="#">
                                <span>Publikasi</span>
                            </a>
                        </li>
                        <li class="nav__item">
                            <a class="nav__link" href="#">
                                <span>Pengumuman</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
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
                                        <li class="nav-tab__item active" data-toggle="tab" data-target="#notification-general-section">
                                            General
                                        </li>
                                        <li class="nav-tab__item" data-toggle="tab" data-target="#notification-following-section">
                                            Following
                                        </li>
                                        <li class="nav-tab__item" data-toggle="tab" data-target="#notification-archive-section">
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
                                                            <img src="{{ Page::quantumAsset('images/example-profile3.jpg') }}" alt="User Avatar" width="44">
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
                                                            <img src="{{ Page::quantumAsset('images/example-profile3.jpg') }}" alt="User Avatar" width="44">
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
                                                    I’ve strated working on a first draft, feel free to take a look and tell me what you think
                                                </p>
                                                <a href="/index.html" download="New File Download.html" class="notification__box">
                                                    <img src="./../../../assets/images/misc-icons/file-document/docx-solid.svg" alt="Docs" class="notifiaction__box-file">
                                                    <span class="notification__box-title">
                                                        Prototype recap.doc
                                                    </span>
                                                    <span class="notification__box-dot"></span>
                                                    <span class="notification__box-info">
                                                        2MB
                                                    </span>
                                                </a>
                                                <a href="/index.html" download="New File Download.html" class="notification__box">
                                                    <img src="./../../../assets/images/misc-icons/file-document/docx-solid.svg" alt="Docs" class="notifiaction__box-file">
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
                                                            <img src="{{ Page::quantumAsset('images/example-profile3.jpg') }}" alt="User Avatar" width="44">
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
                                                    <button type="button" class="btn btn_outline" id="btn-edit-desktop">Button Secondary</button>
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
            </div>
            <div class="dropdown dropdown_nav dropdown_profile">
                <button class="header__user" data-toggle="dropdown">
                    <span class="header__user-wrapper">
                        <span class="header__user-avatar">
                            <span class="avatar avatar_sm">
                                <img src="{{ Page::quantumAsset('images/example-profile3.jpg') }}" alt="User Avatar" width="44">
                            </span>
                        </span>
                        <span class="header__user-info">
                            <span class="header__user-name">Cameron William</span>
                            <span class="header__user-role">Administrator</span>
                        </span>
                    </span>
                    <span class="icon icon-chevron-down-solid"></span>
                </button>
                <div class="dropdown__box dropdown__box_menu-end">
                    <ul class="dropdown__list">
                        <li class="dropdown__avatar">
                            <div class="avatar avatar_sm">
                                <div class="avatar__circle">
                                    <img src="{{ Page::quantumAsset('images/example-profile3.jpg') }}" alt="Avatar Image">
                                </div>
                                <div class="avatar__text">
                                    <span class="avatar__title">Cameron William</span>
                                    <span class="avatar__subtitle">Administrator</span>
                                </div>
                            </div>
                            <div class="progress">
                                <label class="progress__label">Penyimpanan</label>
                                <div class="progress__bar" data-percent-start="0" data-percent-end="60"></div>
                            </div>
                        </li>
                        <li class="dropdown__line"></li>
                        <li class="dropdown__item">
                            <a href="#">
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
                            <a href="#">
                                <span class="icon icon-arrow-left-on-rectangle-mini"></span>
                                Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    <!-- [END] Header -->

    <!-- [START] (if needed | should not be present on the Form Page) Sidebar -->
    <aside class="sidebar sidebar_without-icon">
        <div class="sidebar__group">
            <ul class="sidebar__list">
                <li class="sidebar__item">
                    <a class="sidebar__link" href="./../../../pages/examples/layout/list.html">
                        <span class="icon icon-arrow-left-circle-mini"></span>
                        <span class="sidebar__link-text">Kembali ke List</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidebar__group">
            <div class="sidebar__group-header">
                <span class="sidebar__group-title">Detail</span>
            </div>
            <ul class="sidebar__list">
                <li class="sidebar__item active">
                    <a class="sidebar__link" href="#">
                        <span class="sidebar__link-text">Informasi Umum</span>
                    </a>
                </li>
                <li class="sidebar__item">
                    <a class="sidebar__link" href="#">
                        <span class="sidebar__link-text">Review Proposal</span>
                    </a>
                </li>
                <li class="sidebar__item">
                    <a class="sidebar__link" href="#">
                        <span class="sidebar__link-text">Aktivitas Peneliti</span>
                    </a>
                </li>
                <li class="sidebar__item">
                    <a class="sidebar__link" href="#">
                        <span class="sidebar__link-text">Laporan Progress Report</span>
                    </a>
                </li>
                <li class="sidebar__item">
                    <a class="sidebar__link" href="#">
                        <span class="sidebar__link-text">Output Penelitian</span>
                    </a>
                </li>
                <li class="sidebar__item">
                    <a class="sidebar__link" href="#">
                        <span class="sidebar__link-text">Laporan & Keuangan</span>
                    </a>
                </li>
                <li class="sidebar__item">
                    <a class="sidebar__link" href="#">
                        <span class="sidebar__link-text">Publikasi Penelitian</span>
                    </a>
                </li>
                <li class="sidebar__item">
                    <a class="sidebar__link" href="#">
                        <span class="sidebar__link-text">Summary</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    <!-- [END] Sidebar -->

    <!-- [START] Timeline Sidebar -->
    <aside class="sidebar sidebar_right">
        <h1 class="sidebar__title">Timeline</h1>

        <ul class="stepper stepper_vertical">
            <li class="stepper__item stepper_complete">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">1.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Konfirmasi Anggota</h3>
                    <span class="stepper__item-date">09 Sep – 26 Sep 2023</span>
                </div>
            </li>
            <li class="stepper__item stepper_complete">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">2.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Proposal Diajukan</h3>
                    <span class="stepper__item-date">09 Sep – 26 Sep 2023</span>
                </div>
            </li>
            <li class="stepper__item stepper_complete">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">3.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Review Administrasi</h3>
                    <span class="stepper__item-date">09 Sep – 26 Sep 2023</span>
                </div>
            </li>
            <li class="stepper__item stepper_complete">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">4.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Review Proposal</h3>
                    <span class="stepper__item-date">27 Sep – 17 Okt 2023</span>
                </div>
            </li>
            <li class="stepper__item stepper_agenda stepper_active">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">5.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Seminar Proposal</h3>
                    <span class="stepper__item-date">18 Okt – 24 Okt 2023</span>
                </div>
                <span class="stepper__item-line"></span>

                <div class="stepper__item-content">
                    <div class="stepper__item-icon">
                        <span class="icon icon-calendar-solid"></span>
                    </div>
                    <div class="stepper__item-agenda">
                        <h3 class="stepper__item-agenda-title">Seminar Proposal Penelitian</h3>
                        <span class="stepper__item-agenda-link">meet.google.com/hhq-viih-zan</span>
                    </div>
                    <button type="button" class="btn btn_outline btn_xs">
                        <img src="{{ Page::quantumAsset('images/misc-icons/messengers/google%20meet.svg') }}" width="12" height="12" alt="">
                        Join with Google Meet
                    </button>
                </div>
            </li>
            <li class="stepper__item">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">6.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Pelaksanaan Penelitian</h3>
                    <span class="stepper__item-date">09 Sep – 26 Sep 2023</span>
                </div>
            </li>
            <li class="stepper__item">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">7.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Seminar Progress Report</h3>
                    <span class="stepper__item-date">09 Sep – 26 Sep 2023</span>
                </div>
            </li>
            <li class="stepper__item stepper_agenda">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">8.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Seminar Proposal</h3>
                    <span class="stepper__item-date">26 Sep 2024</span>
                </div>
                <span class="stepper__item-line"></span>

                <div class="stepper__item-content">
                    <div class="stepper__item-icon">
                        <span class="icon icon-calendar-solid"></span>
                    </div>
                    <div class="stepper__item-agenda">
                        <h3 class="stepper__item-agenda-title">Seminar Proposal Penelitian</h3>
                        <span class="stepper__item-agenda-link">meet.google.com/hhq-viih-zan</span>
                    </div>
                    <button type="button" class="btn btn_outline btn_xs">
                        <img src="{{ Page::quantumAsset('images/misc-icons/messengers/google%20meet.svg') }}" width="12" height="12" alt="">
                        Join with Google Meet
                    </button>
                </div>
            </li>
            <li class="stepper__item">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">9.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Input Outcome & Publikasi</h3>
                    <span class="stepper__item-date">Okt 2023</span>
                </div>
            </li>
            <li class="stepper__item">
                <div class="stepper__item-order">
                    <span class="stepper__item-order-number">10.</span>
                </div>
                <div class="stepper__item-title">
                    <h3 class="stepper__item-name">Selesai</h3>
                    <span class="stepper__item-date">Okt 2023</span>
                </div>
            </li>
        </ul>
    </aside>
    <!-- [END] Timeline Sidebar-->

    <!-- [START] (for all layouts) Main Content -->
    <main class="main">
        <div class="container">
            <!-- [START] Header on Main Content -->
            <div class="main__header">
                <div class="main__location">
                    <ul class="breadcrumb">
                        <li class="breadcrumb__item">
                            <a href="./../../../pages/examples/layout/list.html">
                                <span class="icon icon-home-solid"></span>
                            </a>
                        </li>
                        <li class="breadcrumb__item">
                            <a href="#">
                                Beranda
                            </a>
                        </li>
                        <li class="breadcrumb__item">Pengajuan Pendanaan</li>
                        <li class="breadcrumb__item active">Detail</li>
                    </ul>
                    <div class="main__wrapper">
                        <h1 class="main__title">Informasi Umum</h1>
                        <!-- <p class="main__subtitle">Detail Mahasiswa</p> -->
                    </div>
                </div>
            </div>
            <!-- [END] Header on Main Content -->

            <div class="card card_details-primary">
                <div class="grid">
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">Judul Penelitian</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value">Pengaruh Gaya Kepemimpinan Terhadap Kinerja Karyawan: Studi pada Perusahaan Manufaktur</span>
                    </div>
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">Periode</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value">2023</span>
                    </div>
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">Usulan Biaya</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value">Rp156.248.000,00</span>
                    </div>
                    <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                        <label class="row-data__name">Status</label>
                    </div>
                    <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                        <span class="row-data__colon">:</span>
                        <span class="row-data__value">
                            <!-- <span class="badge badge_default"> -->
                                1. Konfirmasi Anggota
                            <!-- </span> -->
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card Pernyataan Penelitian -->
            <div class="card card_details-default">
                <div class="card__header">
                    <div class="card__header-left">
                        <div class="card__header-block">
                            <h2 class="header__title">Pernyataan Penelitian</h2>
                            <span class="header__subtitle">Informasi tentang penelitian yang akan Anda lakukan, mulai dari judul hingga kontribusi penelitian</span>
                        </div>
                    </div>
                   <div class="card__header-right">
                        <button type="button" class="btn btn_outline" id="btn-edit-desktop">
                            <span class="icon icon-pencil-square-solid"></span>
                            Ubah Data
                        </button>
                        <button type="button" class="btn btn_outline btn_icon" id="btn-edit-mobile">
                            <span class="icon icon-pencil-square-solid"></span>
                        </button>
                   </div>
                </div>
                <div class="card__body">
                    <div class="grid">
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Jenis Pendanaan</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Penelitian</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Judul Penelitian</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Pengaruh Gaya Kepemimpinan Terhadap Kinerja Karyawan: Studi pada Perusahaan Manufaktur</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Bidang Ilmu</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Ekonomi & Bisnis</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Nama Lengkap</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Devi Asri Monica Helawa</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Tema</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Generasi Milenial & Isu isu Politik</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Periode</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">2023</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Sumber Pendanaan</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Bantuan Operasional Perguruan Tinggi Negeri (BOPTN)</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Pengelola Pendanaan</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">LPPM Universitas Negeri Surabaya</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Klaster Pendanaan</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">20308 - Pengabdian kepada Masyarakat Berbasis Program Studi 2023</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Output Penelitian</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">HAKI, Bahan Ajar, Jurnal</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Apakah penelitian ini berkontribusi Terhadap Pengembangan Keilmuan Prodi?</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Berkontribusi</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Isian Proposal -->
            <div class="card card_details-default">
                <div class="card__header">
                    <div class="card__header-left">
                        <div class="card__header-block">
                            <h2 class="header__title">Isian Proposal</h2>
                            <span class="header__subtitle">Data proposal yang menjelaskan mulai dari latar belakang, tujuan, metode, dan rencana pelaksanaan proyek</span>
                        </div>
                    </div>
                   <div class="card__header-right">
                        <button type="button" class="btn btn_outline" id="btn-edit-desktop">
                            <span class="icon icon-pencil-square-solid"></span>
                            Ubah Data
                        </button>
                        <button type="button" class="btn btn_outline btn_icon" id="btn-edit-mobile">
                            <span class="icon icon-pencil-square-solid"></span>
                        </button>
                   </div>
                </div>
                <div class="card__body">
                    <div class="grid">
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Latar Belakang</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">
                                Kinerja karyawan adalah faktor kunci dalam kesuksesan perusahaan, terutama dalam industri manufaktur yang memiliki tantangan dan persaingan yang tinggi. Salah satu faktor yang berpengaruh signifikan terhadap kinerja karyawan adalah gaya kepemimpinan yang diterapkan oleh pemimpin perusahaan. Gaya kepemimpinan yang efektif dapat meningkatkan motivasi, produktivitas, dan komitmen karyawan, sementara gaya kepemimpinan yang tidak tepat dapat mengakibatkan penurunan kinerja, konflik, dan turnover yang tinggi.
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Rumusan Masalah</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">
                                Berdasarkan latar belakang di atas, rumusan masalah penelitian ini adalah sebagai berikut: Bagaimana pengaruh gaya kepemimpinan yang diterapkan oleh pemimpin perusahaan manufaktur terhadap kinerja karyawan?
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Tujuan Penelitian</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">
                                Tujuan utama dari penelitian ini adalah untuk:
                                <ol>
                                    <li>Menganalisis dan memahami pengaruh gaya kepemimpinan terhadap kinerja karyawan di perusahaan manufaktur.</li>
                                    <li>Mengidentifikasi gaya kepemimpinan yang paling efektif dalam meningkatkan kinerja karyawan.</li>
                                </ol>
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Kajian Penelitian Terdahulu</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">
                                Penelitian terdahulu tentang gaya kepemimpinan dan kinerja karyawan akan diulas untuk memberikan landasan teoritis dan pemahaman yang lebih baik tentang topik ini. Penelitian yang relevan akan mencakup studi-studi tentang berbagai gaya kepemimpinan, termasuk transformasional, transaksional, dan lainnya, serta dampaknya pada kinerja karyawan.
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Konsep atau Teori Relevan</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">
                                Konsep atau teori yang akan digunakan dalam analisis melibatkan berbagai aspek kepemimpinan, termasuk pemahaman tentang gaya kepemimpinan yang efektif dan peran pemimpin dalam membentuk budaya organisasi yang mendukung kinerja karyawan.
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Metode & Teknik Pengumpulan Data</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">
                                Penelitian ini akan menggunakan metode penelitian kuantitatif dengan pengumpulan data melalui survei. Instrumen survei akan mencakup pertanyaan tentang gaya kepemimpinan yang diterapkan oleh pemimpin perusahaan dan evaluasi kinerja karyawan. Data akan dianalisis menggunakan teknik statistik seperti analisis regresi untuk mengidentifikasi hubungan antara variabel-variabel tersebut.
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Rencana Pembahasan</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">
                                Hasil penelitian akan dibahas dalam beberapa tahap, termasuk:
                                <ol>
                                    <li>Analisis data untuk mengidentifikasi pengaruh gaya kepemimpinan pada kinerja karyawan.</li>
                                    <li>Diskusi tentang implikasi temuan dalam konteks perusahaan manufaktur.</li>
                                    <li>Saran dan rekomendasi untuk pemimpin perusahaan manufaktur dalam meningkatkan kinerja karyawan.</li>
                                </ol>
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Daftar Pustaka</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">
                                Bass, B. M., & Riggio, R. E. (2006). Transformational leadership (2nd ed.). Psychology Press. <br/>
                                Avolio, B. J., & Bass, B. M. (2004). Multifactor leadership questionnaire. Mind Garden. <br/>
                                Yukl, G. (2013). Leadership in organizations (8th ed.). Pearson.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Data Peneliti -->
            <div class="card card_details-default">
                <div class="card__header">
                    <div class="card__header-left">
                        <div class="card__header-block">
                            <h2 class="header__title">Data Peneliti</h2>
                            <span class="header__subtitle">Detail tim yang terlibat dalam penelitian Anda</span>
                        </div>
                    </div>
                   <div class="card__header-right">
                        <button type="button" class="btn btn_outline" id="btn-edit-desktop">
                            <span class="icon icon-pencil-square-solid"></span>
                            Ubah Data
                        </button>
                        <button type="button" class="btn btn_outline btn_icon" id="btn-edit-mobile">
                            <span class="icon icon-pencil-square-solid"></span>
                        </button>
                   </div>
                </div>
                <div class="card__body">
                    <div class="grid">
                        <div class="col-12 col-sm-8 col-md-9 col-lg-12">
                            <h3 class="row-data__title">Data Ketua</h3>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Nama Lengkap</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">2201015062236 - Imas Maesaroh</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">NIP</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">890778215928</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Asal Institusi</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Universitas Negeri Surabaya</span>
                        </div>

                        <div class="col-12 col-sm-8 col-md-9 col-lg-12">
                            <h3 class="row-data__title">Data Anggota Dosen 1</h3>

                            <span class="badge badge_outline-warning badge_sm">
                                Menunggu Persetujuan
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Nama Lengkap</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">2201015062236 - Ayu Pramesti</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">NIP</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">890778215764</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Asal Institusi</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Universitas Negeri Surabaya</span>
                        </div>

                        <div class="col-12 col-sm-8 col-md-9 col-lg-12">
                            <h3 class="row-data__title">Data Anggota Dosen 2</h3>
                            <span class="badge badge_outline-warning badge_sm">
                                Menunggu Persetujuan
                            </span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Nama Lengkap</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">2201015062236 - Daffa Nauransyah</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">NIP</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">890778215765</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Asal Institusi</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Universitas Negeri Surabaya</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Detail Pendanaan -->
            <div class="card card_details-default">
                <div class="card__header">
                    <div class="card__header-left">
                        <div class="card__header-block">
                            <h2 class="header__title">Detail Pendanaan</h2>
                            <span class="header__subtitle">Informasi keuangan yang berkaitan dengan pengelolaan dana untuk mendukung penelitian</span>
                        </div>
                    </div>
                   <div class="card__header-right">
                        <button type="button" class="btn btn_outline" id="btn-edit-desktop">
                            <span class="icon icon-pencil-square-solid"></span>
                            Ubah Data
                        </button>
                        <button type="button" class="btn btn_outline btn_icon" id="btn-edit-mobile">
                            <span class="icon icon-pencil-square-solid"></span>
                        </button>
                   </div>
                </div>
                <div class="card__body">
                    <div class="grid">
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Usulan biaya</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9 col-block_blue">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Rp156.248.000,00</span></span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Nama Bank</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Bank Mandiri</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Nomor Rekening</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">1400056789215</span>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <label class="row-data__name">Nama Pemilik Rekening</label>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9 col-lg-9">
                            <span class="row-data__colon">:</span>
                            <span class="row-data__value">Mayang Larasati</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Dokumen -->
            <div class="box-table__content" id="table-docs">
                <div class="table-max">
                    <table>
                        <thead>
                            <tr>
                                <th class="cell-sorting cell-sorting_active-asc">Name</th>
                                <th class="cell-sorting cell-sorting_active-asc">Size</th>
                                <th class="cell-sorting cell-sorting_active-desc">Modified</th>
                                <th class="cell-action cell-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="cell-inline">
                                    <img src="{{ Page::quantumAsset('images/misc-icons/file-document/pdf-solid.svg') }}" width="24" height="24" alt="">
                                    Proposal-Penelitian.pdf
                                </td>
                                <td>2.3 MB</td>
                                <td>7 min ago</td>
                                <td class="cell-action">
                                    <div class="dropdown-group">
                                        <a href="/#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Download">
                                            <span class="icon icon-arrow-down-tray-solid"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="cell-inline">
                                    <img src="{{ Page::quantumAsset('images/misc-icons/file-document/pdf-solid.svg') }}" width="24" height="24" alt="">
                                    RAB-Penelitian.pdf
                                </td>
                                <td>243 KB</td>
                                <td>10 min ago</td>
                                <td class="cell-action">
                                    <div class="dropdown-group">
                                        <a href="/#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Download">
                                            <span class="icon icon-arrow-down-tray-solid"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- [START] Footer on Main Content -->
        <footer class="footer">
            <div class="footer__copyright-wrapper">
                <div class="footer__logo-wrapper">
                    <img src="{{ Page::quantumAsset('images/logos/sevima.png') }}" alt="" class="footer__logo">
                </div>
                <p class="footer__copyright">
                    © 2005-2023 SEVIMA. All Rights Reserved
                </p>
            </div>
            <p class="footer__brand-name">
                SEVIMA Platform
            </p>
        </footer>
        <!-- [END] Footer on Main Content -->
    </main>
    <!-- [END] Main Content -->

    <!-- [START] Core script -->
    <script type="text/javascript" src="{{ Page::quantumAsset('release/qn-202407120001.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/validation.js') }}"></script>
    <!-- [END] Core script -->
</body>

</html>
