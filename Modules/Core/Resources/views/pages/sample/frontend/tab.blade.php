<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LITABMAS | Detail Pengajuan Pendanaan</title>
    <link rel="icon" href="{{ asset('images/icon-sevima-platform.png') }}">
    <!-- Select Choices Style -->
    <link href="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <!-- Select Choices Script -->
    <script type="text/javascript" src="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/scripts/choices.min.js') }}"></script>
    <link href="{{ Page::quantumAsset('release/qn-202407120001.css') }}" rel="stylesheet">
    <style>
        .box-table__header.header_tab {
            padding: 0;
        }

        .nav-tab {
            border-radius: 12px 12px 0 0;
            border-bottom: 1px solid #e3e8ef;
        }

        .nav-tab .nav-tab__wrapper { 
            padding: 0.25rem 1rem 0;
        }

        .nav-tab .nav-tab__item {
            padding: 0.75rem 1rem;
        }
        
        .tab-pane {
            padding: 0;
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
                                                    <button type="button" class="btn btn_outline">Button Secondary</button>
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

    <main class="main">
        <div class="container">
            <!-- [START] Header on Main Content -->
            <div class="main__header">
                <div class="main__location">
                    <ul class="breadcrumb">
                        <li class="breadcrumb__item">
                            <a href="./../../../pages/examples/layout/table.html">
                                <span class="icon icon-home-solid"></span>
                            </a>
                        </li>
                        <li class="breadcrumb__item active">Pengajuan Pendanaan</li>
                    </ul>
                    <div class="main__wrapper">
                        <h1 class="main__title">Pengajuan Pendanaan</h1>
                        <!-- <p class="main__subtitle">Daftar Mahasiswa</p> -->
                    </div>
                </div>
                <div class="main__action">
                    <a href="./../../../pages/examples/layout/form.html" class="btn btn_primary">
                        <span class="icon icon-plus-circle-solid"></span>
                        <span class="btn__text">Ajukan Proposal</span>
                    </a>
                </div>
            </div>
            <!-- [END] Header on Main Content -->

            <div class="card card_table">
                <div class="card__body">
                    <div class="box-table">
                        <div class="box-table__header header_tab">
                            <nav class="nav-tab">
                                <ul class="nav-tab__wrapper">
                                    <li class="nav-tab__item" data-toggle="tab" data-target="#penelitian">
                                        Penelitian
                                    </li>
                                    <li class="nav-tab__item active" data-toggle="tab" data-target="#pengabdian-masyarakat">
                                        Pengabdian Masyarakat
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        
                        <div class="tab-content">
                            <div class="tab-pane" id="penelitian">
                                <div class="box-table__header">
                                    <div class="grid">
                                        <div class="col-12 col-sm-4 col-md-3">
                                            <div class="form-control">
                                                <div class="form-control__group">
                                                    <span data-input-icon="search"></span>
                                                    <input type="search" class="form-control__input" placeholder="Cari data penelitian"> 
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-8 col-md-9">
                                            <div class="box-table__wrapper">
                                                <div class="grid cols-1 cols-sm-2 cols-md-3">
                                                    <div class="col-start-sm-1 col-start-md-2">
                                                        <select class="select-search">
                                                            <option selected disabled>Search Something</option>
                                                            <option value="List Items 1">List Items 1</option>
                                                            <option value="List Items 2">List Items 2</option>
                                                            <option value="List Items 3">List Items 3</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-table__content">
                                    <div class="table-max table-max_absolute table_striped">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th class="cell-check cell-center">
                                                        <input type="checkbox" class="check-all-item" name="group">
                                                    </th>
                                                    <th class="cell-sorting cell-sorting_active-desc">NIM</th>
                                                    <th class="cell-sorting cell-sorting_active-asc">Nama Lengkap</th>
                                                    <th class="cell-sorting cell-sorting_active-desc">Jenjang</th>
                                                    <th class="cell-sorting cell-sorting_active-desc">Program Studi</th>
                                                    <th class="cell-sorting">Status</th>
                                                    <th class="cell-sorting">Semester</th>
                                                    <th class="cell-sorting">SKS</th>
                                                    <th class="cell-sorting">IPK</th>
                                                    <th class="cell-action cell-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Mayang Larasati
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Tri Meida Ratnawati
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Gigih Hadi
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Fahmi Akbar
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Atmayanti
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="box-table__footer">
                                    <div class="box-table__action">
                                        <div class="box-table__action-left">
                                            <p class="box-table__result">
                                                Menampilkan <b>1-10</b> dari Total 235 data
                                            </p>
                                        </div>
                                        <div class="box-table__action-right">
                                            <div class="form-control form-control_stand-alone">
                                                <select class="select-default" id="select-search-4">
                                                    <option value="">20 Baris</option>
                                                    <option value="">40 Baris</option>
                                                    <option value="">100 Baris</option>
                                                </select>
                                            </div>
                                            <div id="paginate"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane active" id="pengabdian-masyarakat">
                            <div class="box-table__header">
                                    <div class="grid">
                                        <div class="col-12 col-sm-4 col-md-3">
                                            <div class="form-control">
                                                <div class="form-control__group">
                                                    <span data-input-icon="search"></span>
                                                    <input type="search" class="form-control__input" placeholder="Cari judul proposal pengabdian"> 
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-8 col-md-9">
                                            <div class="box-table__wrapper">
                                                <div class="grid cols-1 cols-sm-2 cols-md-3">
                                                    <div class="col-start-sm-1 col-start-md-2">
                                                        <select class="select-search">
                                                            <option selected disabled>Search Something</option>
                                                            <option value="List Items 1">List Items 1</option>
                                                            <option value="List Items 2">List Items 2</option>
                                                            <option value="List Items 3">List Items 3</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box-table__content">
                                    <div class="table-max table-max_absolute table_striped">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th class="cell-check cell-center">
                                                        <input type="checkbox" class="check-all-item" name="group">
                                                    </th>
                                                    <th class="cell-sorting cell-sorting_active-desc">NIM</th>
                                                    <th class="cell-sorting cell-sorting_active-asc">Nama Lengkap</th>
                                                    <th class="cell-sorting cell-sorting_active-desc">Jenjang</th>
                                                    <th class="cell-sorting cell-sorting_active-desc">Program Studi</th>
                                                    <th class="cell-sorting">Status</th>
                                                    <th class="cell-sorting">Semester</th>
                                                    <th class="cell-sorting">SKS</th>
                                                    <th class="cell-sorting">IPK</th>
                                                    <th class="cell-action cell-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Mayang Larasati
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Tri Meida Ratnawati
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Gigih Hadi
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Fahmi Akbar
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="cell-check cell-center">
                                                        <input type="checkbox" class="form-control__checkbox check-item" name="group">
                                                    </td>
                                                    <td>
                                                        1455201001
                                                    </td>
                                                    <td>
                                                        Atmayanti
                                                    </td>
                                                    <td>Strata 1</td>
                                                    <td>Sistem Informasi</td>
                                                    <td>A</td>
                                                    <td>10</td>
                                                    <td>2</td>
                                                    <td>3.83</td>
                                                    <td class="cell-action">
                                                        <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                                            <a href="./../../../pages/examples/layout/detail.html" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                                <span class="icon icon-eye-solid"></span>
                                                            </a>
                                                            <a href="#" class="btn btn_outline btn_xs btn_icon" data-btn-label="Hapus">
                                                                <span class="icon icon-trash-solid"></span>
                                                            </a>
                                                            <div class="dropdown">
                                                                <button class="btn btn_outline btn_xs btn_icon" data-toggle="dropdown">
                                                                    <span class="icon icon-ellipsis-horizontal"></span>
                                                                </button>
                                                                <ul class="dropdown__list dropdown__list_menu-end">
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 1</a>
                                                                    </li>
                                                                    <li class="dropdown__item">
                                                                        <a href="#">Lainnya 2</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown-group__target">
                                                                <div class="dropdown-group__toggle">
                                                                    <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                        <span class="icon icon-ellipsis-horizontal"></span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="box-table__footer">
                                    <div class="box-table__action">
                                        <div class="box-table__action-left">
                                            <p class="box-table__result">
                                                Menampilkan <b>1-10</b> dari Total 235 data
                                            </p>
                                        </div>
                                        <div class="box-table__action-right">
                                            <div class="form-control form-control_stand-alone">
                                                <select class="select-default" id="select-search-4">
                                                    <option value="">20 Baris</option>
                                                    <option value="">40 Baris</option>
                                                    <option value="">100 Baris</option>
                                                </select>
                                            </div>
                                            <div id="paginate-2"></div>
                                        </div>
                                    </div>
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
    <!-- [END] Main Content -->

    <!-- [START] Core script -->
    <script type="text/javascript" src="{{ Page::quantumAsset('release/qn-202407120001.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/validation.js') }}"></script>    

    <script>
        // [START] Pagination
        let page = new Pagination(document.getElementById("paginate"), {
            url: "http://127.0.0.1:5502/pages/examples/layout/test.html?page=<<$page>>",
            total: 30,
            current: 5,
        });

        let page2 = new Pagination(document.getElementById("paginate-2"), {
            url: "http://127.0.0.1:5502/pages/examples/layout/test.html?page=<<$page>>",
            total: 30,
            current: 5,
        });
        // [END] Pagination
    </script>

    <!-- [END] Core script -->
</body>

</html>