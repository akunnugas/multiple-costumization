<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMI | Detail Pengajuan Pendanaan</title>
    <link rel="icon" href="{{ asset('images/icon-sevima-platform.png') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ Page::quantumAsset('release/qn-202407120001.css') }}" >
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
                                <span>Dokumentasi SPMI</span>
                            </a>
                        </li>
                        <li class="nav__item">
                            <div class="dropdown dropdown_nav">
                                <a class="nav__link" href="#" data-toggle="dropdown">
                                    <span>AMI</span>
                                    <span class="icon icon-chevron-down-mini"></span>
                                </a>
                                <div class="dropdown__box">
                                    <ul class="dropdown__list">
                                        <li class="dropdown__item">
                                            <a href="./../../../pages/docs/components/alert.html">Basics</a>
                                        </li>
                                        <li class="dropdown__item">
                                            <a href="./../../../pages/docs/aplication-component/modal.html">Applications</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="nav__item">
                            <div class="dropdown dropdown_nav">
                                <a class="nav__link" href="#" data-toggle="dropdown">
                                    <span>Detail Pendukung</span>
                                    <span class="icon icon-chevron-down-mini"></span>
                                </a>
                                <div class="dropdown__box">
                                    <ul class="dropdown__list">
                                        <li class="dropdown__item">
                                            <a href="./../../../pages/docs/components/alert.html">Basics</a>
                                        </li>
                                        <li class="dropdown__item">
                                            <a href="./../../../pages/docs/aplication-component/modal.html">Applications</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
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

    <!-- [START] (for all layouts) Main -->
    <main class="main">
        <div class="container">
            <!-- [END] Header on Main Content -->
            <div class="card">
                <div class="card__header">
                    <ul class="breadcrumb">
                        <li class="breadcrumb__item">
                            <span class="icon icon-home-mini"></span>
                        </li>
                        <li class="breadcrumb__item">
                            <a href="#">Beranda</a>
                        </li>
                        <li class="breadcrumb__item">
                            <a href="#">Dokumen SPMI</a>
                        </li>
                        <li class="breadcrumb__item active">
                            Laporan Kinerja
                        </li>
                    </ul>

                    <div class="button-group">
                        <div class="button-group__left">
                            <button type="button" class="btn btn_outline btn_xs">
                                Kembali ke List
                            </button>

                            <button type="button" class="btn btn_primary btn_xs">
                                Ubah Dokumen
                            </button>
                        </div>


                        <!-- <div class="button-group__right">
                            <button type="button" class="btn btn_outline btn_icon btn_xs btn_invisible">
                                <span class="icon icon-ellipsis-horizontal-solid"></span>
                            </button>

                            <button type="button" class="btn btn_outline btn_icon btn_xs btn_invisible">
                                <img src="{{ asset('images/icon/icon-minimize.svg'); }}" alt="">
                            </button>
                        </div>  -->

                        <div class="button-group__mobile">
                            <button type="button" class="btn btn_outline btn_icon btn_xs">
                                <span class="icon icon-arrow-left-solid"></span>
                            </button>

                            <button type="button" class="btn btn_primary btn_icon btn_xs">
                                <span class="icon icon-pencil-solid"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card__body">
                    <!-- Sidebar -->
                    <div class="sidebar sidebar_within">
                        <div class="sidebar__header">
                            <h3 class="sidebar__title">
                                Laporan Kinerja
                            </h3>

                            <div class="sidebar__action">
                                <button type="button" class="btn btn_icon">
                                    <img src="{{ asset('images/icon/icon-layout.svg'); }}" alt="">
                                </button>
                            </div>
                        </div>
                        <ul class="sidebar__list">
                            <li class="sidebar__item">
                                <a class="sidebar__link active" href="/pages/docs/layouts/basic-layout.html">
                                    <span class="sidebar__link-text">Tabel Pengisian</span>
                                </a>
                            </li>
                            <li class="sidebar__item">
                                <a class="sidebar__link" href="/pages/docs/layouts/signin-signup.html">
                                    <span class="sidebar__link-text">Nama Kolom</span>
                                </a>
                            </li>
                            <li class="sidebar__item">
                                <a class="sidebar__link" href="/pages/docs/layouts/list-data.html">
                                    <span class="sidebar__link-text">Nama Baris</span>
                                </a>
                            </li>
                            <li class="sidebar__item">
                                <a class="sidebar__link" href="/pages/docs/layouts/create-data.html">
                                    <span class="sidebar__link-text">Label Sel</span>
                                </a>
                            </li>
                            <li class="sidebar__item">
                                <a class="sidebar__link" href="/pages/docs/layouts/detail-data.html">
                                    <span class="sidebar__link-text">Footer Tabel</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- Content -->
                    <div class="content">
                        <div class="content__header">
                            <h3 class="content__title">
                                Tabel Pengisian Laporan Kinerja
                            </h3>
                        </div>

                        <div class="content__body">

                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Laporan Kinerja</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value"><span class="row-data__colon">:</span>  Instrumen Akreditasi Program Studi (IAPS) Versi 4.0</span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Parent Tabel</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value"><span class="row-data__colon">:</span>  1 - 1. Tata Pamong, Tata Kelola dan Kerja Sama</span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Nomor Tabel</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value"><span class="row-data__colon">:</span>  1a</span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Nama Tabel</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value"><span class="row-data__colon">:</span>  a. Kerja Sama</span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Jenis Tabel</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value"><span class="row-data__colon">:</span>  Label</span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Hak Akses Pengisian</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value"><span class="row-data__colon">:</span>  Tambah</span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Jenis Form Pengisian</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value"><span class="row-data__colon">:</span>  Text Area</span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-4 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Status Tabel</label>
                                </div>
                                <div class="col-8 col-sm-8 col-md-8 col-lg-8">
                                    <span class="row-data__value"><span class="row-data__colon">:</span>
                                        <span class="badge badge_secondary-success badge_sm">
                                            Aktif
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-12 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Uraian</label>
                                </div>
                                <div class="col col-md-8 col-lg-8">
                                    <span class="row-data__colon">:</span>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <span class="row-data__value">
                                        <span class="row-data__colon colon_paragraph">:</span>
                                        Tuliskan kerja sama tridharma di Unit Pengelola Program Studi (UPPS) dalam 3 tahun terakhir dengan mengikuti format Tabel 1 berikut ini. Tabel 1 Kerja Sama Tridharma
                                    </span>
                                </div>
                            </div>

                            <div class="grid">
                                <div class="col-12 col-sm-4 col-md-4 col-lg-4">
                                    <label class="row-data__name">Keterangan</label>
                                </div>
                                <div class="col col-md-8 col-lg-8">
                                    <span class="row-data__colon">:</span>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <span class="row-data__value">
                                        <span class="row-data__colon colon_paragraph">:</span>
                                        Keterangan:1) Beri tanda V pada kolom yang sesuai.2) Diisi dengan judul kegiatan kerja sama yang sudah terimplementasikan, melibatkan sumber daya dan memberikan manfaat bagi Program Studi yang diakreditasi.3) Bukti kerja sama dapat berupa Surat Penugasan, Surat Perjanjian Kerja Sama (SPK), bukti-bukti pelaksanaan (laporan, hasil kerja sama, luaran kerja sama), atau bukti lain yang relevan. Dokumen Memorandum of Understanding (MoU), Memorandum of Agreement (MoA), atau dokumen sejenis yang memayungi pelaksanaan kerja sama, tidak dapat dijadikan bukti realisasi kerja sama.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <!-- [END] (for all layouts) Main -->


    <!-- [START] Core script -->
    <script type="text/javascript" src="{{ Page::quantumAsset('release/qn-202407120001.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/validation.js') }}"></script>
    <!-- [END] Core script -->


    <script>
        const sidebarWithin = document.querySelector('.sidebar.sidebar_within');
        const mainContent2 = document.querySelector('.content');

        document.querySelector('.sidebar__action button').onclick = function () {
            sidebarWithin.classList.toggle('sidebar_within-collapsed');
            mainContent2.classList.toggle('content_large');

        }
    </script>

</body>

</html>
