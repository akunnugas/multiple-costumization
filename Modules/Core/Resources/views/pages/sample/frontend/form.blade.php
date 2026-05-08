<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LITABMAS | Detail Pengajuan Pendanaan</title>
    <link rel="icon" href="{{ asset('images/icon-sevima-platform.png') }}">
    <link href="{{ Page::quantumAsset('release/qn-202407120001.css') }}" rel="stylesheet">
    <!-- Select Choices Style -->
    <link href="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/styles/choices.min.css') }}" rel="stylesheet">
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

        .btn_outline.btn_icon.btn_sm[aria-expanded="true"] .icon {
            transform: rotate(180deg);
        }

    </style>
</head>
<body>
    <!-- [START] (for all layouts) Header -->
    <header class="header header_position-static">
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
        <!-- [START] Form -->
        <form action="javascript:void(0)" method="post">
            <div class="form-nav">
                <div class="form-nav__left">
                    <a href="./../../../pages/examples/layout/list.html" class="btn btn_outline">
                        <span class="icon icon-arrow-left-mini"></span>
                        <span class="btn__text">Kembali</span>
                    </a>
                </div>
                <div class="form-nav__middle">
                    <ul class="form-nav__breadcrumb">
                        <li class="form-nav__breadcrumb-item">
                            <a href="#" class="form-nav__breadcrumb-btn">Mahasiswa</a>
                        </li>
                        <li class="form-nav__breadcrumb-diagonal"></li>
                        <li class="form-nav__breadcrumb-item active">
                            Tambah
                        </li>
                    </ul>
                </div>
                <div class="form-nav__right">
                    <div class="form-nav__wrapper">
                        <span class="form-nav__timestamp">
                            <span class="icon icon-check"></span>
                            Saved 2 min ago
                        </span>
                        <div class="form-nav__button-wrapper">
                            <button type="submit" class="btn btn_primary">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-table">
                <h4 class="form-table__title">Table of Content</h4>
                <ul class="form-table__list">
                    <li class="form-table__item">
                        <a href="#card-form-1" class="form-table__anchor">Data Mahasiswa</a>
                    </li>
                    <li class="form-table__item">
                        <a href="#card-form-2" class="form-table__anchor">Informasi Umum</a>
                    </li>
                    <li class="form-table__item">
                        <a href="#card-form-3" class="form-table__anchor">Domisili atau Wilayah</a>
                    </li>
                    <li class="form-table__item">
                        <a href="#card-form-4" class="form-table__anchor">Data Orang Tua</a>
                    </li>
                    <li class="form-table__item">
                        <a href="#card-form-5" class="form-table__anchor">Data Wali</a>
                    </li>
                    <li class="form-table__item">
                        <a href="#card-form-6" class="form-table__anchor">Pendidikan Terakhir</a>
                    </li>
                    <li class="form-table__item">
                        <a href="#card-form-7" class="form-table__anchor">Lainnya</a>
                    </li>
                </ul>
            </div>

            <div class="container">
                <!-- [START] Header on Main Content -->
                <div class="main__header">
                    <div class="main__location">
                        <ul class="breadcrumb">
                            <li class="breadcrumb__item">
                                <a href="#">
                                    <span class="icon icon-home-solid"></span>
                                </a>
                            </li>
                            <li class="breadcrumb__item">
                                <a href="#">
                                    Portal
                                </a>
                            </li>
                            <li class="breadcrumb__item active">Mahasiswa</li>
                        </ul>
        
                        <div class="main__wrapper">
                            <h1 class="main__title">Tambah Mahasiswa</h1>
                        </div>
                    </div>
                </div>
                <!-- [END] Header on Main Content -->

                <div class="grid">
                        <div class="col-12">
                            <div class="card card_form" id="card-form-1">
                                <div class="card__header">
                                    <div class="form-header">
                                        <div class="form-header__wrapper">
                                            <div class="form-header__avatar">
                                                <span class="icon icon-user-mini"></span>
                                            </div>
                                            <div class="form-header__information">
                                                <h3 class="form-header__title">Data Mahasiswa</h3>
                                                <p class="form-header__subtitle">Informasi umum mahasiswa.</p>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn_outline btn_icon btn_sm show" role="button" data-toggle="collapse" data-target="#collapse-mahasiswa">
                                            <span class="icon icon-chevron-down-solid"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="collapse" id="collapse-mahasiswa">
                                    <div class="card__body">
                                        <div class="grid cols-1">
                                            <div class="form-control">
                                                <label for="form-control-nim" class="form-control__label">
                                                    NIM<span class="important">*</span>
                                                </label>
                                                <div class="form-control__group">
                                                    <input type="text" id="form-control-nim" name="nim" class="form-control__input" placeholder="Masukkan NIM" value="1441001101112" required>
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                            <div class="form-control">
                                                <label for="form-control-name" class="form-control__label">
                                                    Nama<span class="important">*</span>
                                                </label>
                                                <div class="form-control__group">
                                                    <input type="text" id="form-control-name" name="name" class="form-control__input" placeholder="Masukkan Nama" value="" required>
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                            <div class="form-control">
                                                <label class="form-control__label">
                                                    Jenis Kelamin<span class="important">*</span>
                                                </label>
                                                <div class="radio-button-inline">
                                                    <input type="radio" class="form-control__radio" id="form-control-jk-lk" name="jk" value="Laki-Laki" checked>
                                                    <label for="form-control-jk-lk" class="form-control__label-radio">Laki-Laki</label>
                                                </div>
                                                <div class="radio-button-inline">
                                                    <input type="radio" class="form-control__radio" id="form-control-jk-pr" value="Perempuan" name="jk">
                                                    <label for="form-control-jk-pr" class="form-control__label-radio">Perempuan</label>
                                                </div>
                                            </div>
                                            <div class="form-control">
                                                <label for="form-control-tempat-lahir" class="form-control__label">
                                                    Tempat Lahir<span class="important">*</span>
                                                </label>
                                                <div class="form-control__group">
                                                    <input type="text" id="form-control-tempat-lahir" name="tempat_lahir" class="form-control__input" placeholder="Masukkan Tempat Lahir" value="" required>
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                            <div class="form-control">
                                                <label for="form-control-tanggal-lahir" class="form-control__label">
                                                    Tanggal Lahir<span class="important">*</span>
                                                </label>
                                                <div class="form-control__group">
                                                    <input type="date" id="form-control-tanggal-lahir" name="tanggal_lahir" class="form-control__input" value="" required>
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                            <hr class="dashed">
                                            <div class="form-control">
                                                <label for="form-control-email" class="form-control__label">
                                                    Email<span class="important">*</span>
                                                </label>
                                                <div class="form-control__group">
                                                    <span data-input-icon="email"></span>
                                                    <input type="email" id="form-control-email" name="email" class="form-control__input" placeholder="Masukkan Email" required>
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                            <div class="form-control">
                                                <label for="form-control-password" class="form-control__label">
                                                    Password
                                                </label>
                                                <div class="form-control__group">
                                                    <input type="password" id="form-control-password" class="form-control__input" name="password" placeholder="Masukkan Password (opsional)">
                                                    <span data-visibility="input" data-type="password"></span>
                                                </div>
                                            </div>
                                            <hr class="dashed">
                                            <div class="form-control">
                                                <label for="form-control-tanggal-masuk" class="form-control__label">
                                                    Waktu dan Tanggal Masuk
                                                </label>
                                                <div class="form-control__group">
                                                    <input type="datetime-local" id="form-control-tanggal-masuk" name="tanggal_masuk" class="form-control__input" value="">
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                            <div class="form-control">
                                                <label for="form-control-alamat" class="form-control__label">
                                                    Alamat Lengkap<span class="important">*</span>
                                                </label>
                                                <div class="form-control__group">
                                                    <Textarea class="form-control__input textarea" id="form-control-alamat" name="alamat" placeholder="Masukkan Alamat Lengkap" required></Textarea>
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                            <hr class="dashed">
                                            <div class="form-control">
                                                <label for="form-control-foto" class="form-control__label">
                                                    Foto
                                                </label>
                                                <div class="upload-draggable">
                                                    <div class="upload-draggable__box">
                                                        <input type="file" class="upload-draggable__file-input" name="foto[]" id="form-control-foto" accept="image/*,.mp4,.mbz,.txt,.myo,.rar" required>
                                                        <label class="upload-draggable__icon"><span class="icon icon-cloud-arrow-up"></span></label>
                                                        <h2 class="upload-draggable__title">Klik untuk pilih file</h2>
                                                        <p class="upload-draggable__subtitle">atau seret file ke sini</p>
                                                        <p class="upload-draggable__support">SVG, PNG, JPG atau GIF (max. 200x300px)</p>
                                                    </div>
                                                    <div class="upload-draggable__uploading">
                                                        <span class="loader"></span> sedang memuat...
                                                    </div>
                                                    <div class="upload-draggable__success">
                                                        Berhasil
                                                    </div>
                                                    <div class="upload-draggable__error">
                                                        Gagal
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-control">
                                                <label for="form-control-portofolio" class="form-control__label">
                                                    Portofolio
                                                </label>
                                                <div class="upload-draggable upload-draggable_inline">
                                                    <div class="upload-draggable__box-inline">
                                                        <input type="file" class="upload-draggable__file-input" name="portofolio[]" id="form-control-portofolio" accept="application/pdf,.ppt,.pptx,.doc,.docx,.xlsx,.xls,.zip">
                                                        <div class="upload-draggable__inline-wrapper">
                                                            <label class="upload-draggable__icon"><span class="icon icon-cloud-arrow-up"></span></label>
                                                            <div class="upload-draggable__wrapper">
                                                                <h2 class="upload-draggable__title">Klik untuk pilih file <span class="upload-draggable__subtitle">atau seret file ke sini</span></h2>
                                                                <p class="upload-draggable__support">PDF, PPT, DOC, XLS atau ZIP (max. 2mb)</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="upload-draggable__success">
                                                        Berhasil
                                                    </div>
                                                </div>                                    
                                            </div>
                                            <div class="form-control">
                                                <label for="form-control-select" class="form-control__label">
                                                    Basic Select
                                                </label>
                                                <div class="form-control__group">
                                                    <select name="select" id="form-control-select" class="form-control__select">
                                                        <option value="" disabled>---- Pilih Salah Satu ----</option>
                                                        <option value="1">Opsi 1</option>
                                                        <option value="2">Opsi 2</option>
                                                        <option value="3">Opsi 3</option>
                                                    </select>
                                                </div>                                    
                                            </div>
                                            <div class="form-control">
                                                <label for="form-control-choices" class="form-control__label">
                                                    Select with Search
                                                </label>
                                                <div class="form-control__group">
                                                    <select name="choices" id="form-control-choices" data-trigger class="form-control__select">
                                                        <option value="" disabled>---- Pilih Salah Satu ----</option>
                                                        <option value="1">Opsi 1</option>
                                                        <option value="2">Opsi 2</option>
                                                        <option value="3">Opsi 3</option>
                                                    </select>
                                                </div>                                    
                                            </div>
                                            <hr class="dashed">
                                            <div class="form-control">
                                                <div class="checkbox">
                                                    <input type="checkbox" class="form-control__checkbox" id="form-control-robot" name="robot">
                                                    <label for="form-control-robot" class="form-control__label-checkbox">Saya bukan robot</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card card_form" id="card-form-2">
                                <div class="card__header">
                                    <div class="form-header">
                                        <div class="form-header__wrapper">
                                            <div class="form-header__avatar">
                                                <span class="icon icon-home-modern-mini"></span>
                                            </div>
                                            <div class="form-header__information">
                                                <h3 class="form-header__title">Informasi Umum</h3>
                                                <p class="form-header__subtitle">Informasi umum mahasiswa.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card__body">
                                    <div class="grid cols-1">
                                        <div class="form-control">
                                            <label for="form-control-sample-1" class="form-control__label">
                                                Sample
                                            </label>
                                            <div class="form-control__group">
                                                <input type="text" id="form-control-sample-1" name="nim" class="form-control__input" placeholder="Hanya Contoh">
                                                <span data-clear="input"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card card_form" id="card-form-3">
                                <div class="card__header">
                                    <div class="form-header">
                                        <div class="form-header__wrapper">
                                            <div class="form-header__avatar">
                                                <span class="icon icon-globe-americas-mini"></span>
                                            </div>
                                            <div class="form-header__information">
                                                <h3 class="form-header__title">Domisili atau Wilayah</h3>
                                                <p class="form-header__subtitle">Informasi umum mahasiswa.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card__body">
                                    <div class="grid cols-1">
                                        <div class="form-control">
                                            <label for="form-control-sample-2" class="form-control__label">
                                                Sample
                                            </label>
                                            <div class="form-control__group">
                                                <input type="text" id="form-control-sample-2" name="nim" class="form-control__input" placeholder="Hanya Contoh">
                                                <span data-clear="input"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card card_form" id="card-form-4">
                                <div class="card__header">
                                    <div class="form-header">
                                        <div class="form-header__wrapper">
                                            <div class="form-header__avatar">
                                                <span class="icon icon-user-circle-mini"></span>
                                            </div>
                                            <div class="form-header__information">
                                                <h3 class="form-header__title">Data Orang Tua</h3>
                                                <p class="form-header__subtitle">Informasi umum mahasiswa.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card__body">
                                    <div class="grid cols-1">
                                        <div class="form-control">
                                            <label for="form-control-sample-3" class="form-control__label">
                                                Sample
                                            </label>
                                            <div class="form-control__group">
                                                <input type="text" id="form-control-sample-3" name="nim" class="form-control__input" placeholder="Hanya Contoh">
                                                <span data-clear="input"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card card_form" id="card-form-5">
                                <div class="card__header">
                                    <div class="form-header">
                                        <div class="form-header__wrapper">
                                            <div class="form-header__avatar">
                                                <span class="icon icon-user-plus-mini"></span>
                                            </div>
                                            <div class="form-header__information">
                                                <h3 class="form-header__title">Data Wali</h3>
                                                <p class="form-header__subtitle">Informasi umum mahasiswa.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card__body">
                                    <div class="grid cols-1">
                                        <div class="form-control">
                                            <label for="form-control-sample-4" class="form-control__label">
                                                Sample
                                            </label>
                                            <div class="form-control__group">
                                                <input type="text" id="form-control-sample-4" name="nim" class="form-control__input" placeholder="Hanya Contoh">
                                                <span data-clear="input"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card card_form" id="card-form-6">
                                <div class="card__header">
                                    <div class="form-header">
                                        <div class="form-header__wrapper">
                                            <div class="form-header__avatar">
                                                <span class="icon icon-academic-cap-mini"></span>
                                            </div>
                                            <div class="form-header__information">
                                                <h3 class="form-header__title">Pendidikan Terakhir</h3>
                                                <p class="form-header__subtitle">Informasi umum mahasiswa.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card__body">
                                    <div class="grid cols-1">
                                        <div class="form-control">
                                            <label for="form-control-sample-5" class="form-control__label">
                                                Sample
                                            </label>
                                            <div class="form-control__group">
                                                <input type="text" id="form-control-sample-5" name="nim" class="form-control__input" placeholder="Hanya Contoh">
                                                <span data-clear="input"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card card_form" id="card-form-7">
                                <div class="card__header">
                                    <div class="form-header">
                                        <div class="form-header__wrapper">
                                            <div class="form-header__avatar">
                                                <span class="icon icon-ellipsis-vertical-mini"></span>
                                            </div>
                                            <div class="form-header__information">
                                                <h3 class="form-header__title">Lainnya</h3>
                                                <p class="form-header__subtitle">Informasi umum mahasiswa.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card__body">
                                    <div class="grid">
                                        <div class="col-12">
                                            <div class="form-control">
                                                <label for="form-control-sample-6" class="form-control__label">
                                                    Sample
                                                </label>
                                                <div class="form-control__group">
                                                    <input type="text" id="form-control-sample-6" name="nim" class="form-control__input" placeholder="Hanya Contoh">
                                                    <span data-clear="input"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- [END] Form -->

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
    <!-- Select Choices Script -->
    <script type="text/javascript" src="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/scripts/choices.min.js') }}"></script>

    <script>
        // [START] Pagination
        let page = new Pagination(document.getElementById("paginate"), {
            url: "http://127.0.0.1:5502/pages/examples/layout/test.html?page=<<$page>>",
            total: 30,
            current: 5,
        });
        // [END] Pagination
    </script>

    <!-- [END] Core script -->
</body>

</html>