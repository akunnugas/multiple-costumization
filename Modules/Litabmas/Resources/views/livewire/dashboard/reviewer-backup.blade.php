@php
    use Modules\Core\Helpers\Date;
    use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;
    use Modules\Gate\Models\Role;
@endphp

<div style="display: flex; flex-direction: column; gap: 1rem;">
    @pushOnce('head')
        {{-- [START] Vendor Flatpickr --}}
        <link rel="stylesheet" href="{{ asset('vendor/flatpickr-4.6.13/dist/flatpickr.min.css') }}">
        <script src="{{ asset('vendor/flatpickr-4.6.13/dist/flatpickr.min.js') }}"></script>
        {{-- [END] Vendor Flatpickr --}}

        @vite('Modules/Litabmas/Resources/assets/sass/dashboard/multi-role.scss')

        @vite('resources/scss/custom-utils.scss')
        {{-- @vite('resources/scss/dashboard.scss') --}}
    @endPushOnce

    {{-- [START] Header --}}
    <div class="main__wrapper">
        <div class="grid" style="z-index: 501">
            <div class="col-12 col-sm-5 col-md-7">
                <h1 class="main__title">Beranda</h1>
            </div>
            <div class="col-12 col-sm-3 col-start-md-7 col-end-md-9">
                <x-core::select
                    style="search"
                    id="select-dropdown-page-period"
                    wire:change="setPeriode(event.target.value)"
                    :selected="$periode"
                    :options="$listPeriode"
                />
            </div>
            <div class="col-12 col-sm-4 col-start-md-9 col-end-md-12">
                <x-core::select
                    style="search"
                    id="select-dropdown-page-type-activity"
                    wire:change="setJenisKegiatan(event.target.value)"
                    :selected="$jenisKegiatan"
                    :options="$listJenisKegiatan"
                />
            </div>
        </div>
    </div>
    {{-- [END] Header --}}

    {{-- [START] Section 1 --}}
    <div class="col-12" wire:ignore>
        <nav-widget module-code="litabmas"></nav-widget>
    </div>

    <div class="grid cols-1 cols-sm-2 cols-md-3" wire:ignore>
        <div class="card card_bordered card_bordered-bg">
            <div class="card__body">
                <div class="card__wrapper">
                    <div class="card__icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"
                             class="card__icon">
                            <path
                                d="M1.875 6.875H18.125M1.875 7.5H18.125M4.375 11.875H9.375M4.375 13.75H6.875M3.75 16.25H16.25C17.2855 16.25 18.125 15.4105 18.125 14.375V5.625C18.125 4.58947 17.2855 3.75 16.25 3.75H3.75C2.71447 3.75 1.875 4.58947 1.875 5.625V14.375C1.875 15.4105 2.71447 16.25 3.75 16.25Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="card__text-wrapper">
                        <h2 class="card__title">
                            Dapatkan pendanaan periode {{ $periodeTerbaru }}<br>
                            untuk Penelitian dan Pengabdian
                        </h2>
                        <p class="card__subtitle">Siapkan ide Proposal Penelitian atau Pengabdian Masyarakat</p>
                    </div>
                </div>
                <a href="{{ route('litabmas.pengajuan-pendanaan.create') }}" class="btn btn_primary btn_xs">
                    Ajukan Proposal
                </a>
            </div>
        </div>
        <div class="card card_bordered card_bordered-bg">
            <div class="card__body">
                <div class="card__wrapper">
                    <div class="card__icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"
                             class="card__icon">
                            <path
                                d="M10.8334 6.6665V13.3332C10.8334 14.7139 9.71416 15.8332 8.33341 15.8332H6.52449C6.1813 16.8042 5.25526 17.4998 4.16675 17.4998C2.78604 17.4998 1.66675 16.3806 1.66675 14.9998C1.66675 13.6191 2.78604 12.4998 4.16675 12.4998C5.25526 12.4998 6.1813 13.1955 6.52449 14.1665H8.33341C8.79366 14.1665 9.16675 13.7934 9.16675 13.3332V6.6665C9.16675 5.2858 10.286 4.1665 11.6667 4.1665H14.1667V1.6665L18.3334 4.99984L14.1667 8.33317V5.83317H11.6667C11.2065 5.83317 10.8334 6.20627 10.8334 6.6665ZM4.16675 15.8332C4.62698 15.8332 5.00008 15.4601 5.00008 14.9998C5.00008 14.5396 4.62698 14.1665 4.16675 14.1665C3.70651 14.1665 3.33341 14.5396 3.33341 14.9998C3.33341 15.4601 3.70651 15.8332 4.16675 15.8332Z"
                                fill="currentColor"/>
                        </svg>
                    </div>
                    <h2 class="card__title">
                        Panduan Pengajuan Proposal Penelitian<br>
                        dan Pengabdian Masyarakat {{ $periodeTerbaru }}
                    </h2>
                </div>
                <button data-toggle="modal" data-target="#modal-panduan" type="button" class="btn btn_outline btn_xs">
                    <span class="icon icon-arrow-down-tray-mini"></span>
                    Lihat Panduan
                </button>
            </div>
        </div>
        <div class="col-1 col-sm-2 col-md-1">
            <div class="card card_bordered card_bordered-bg">
                <div class="card__body">
                    <div class="card__wrapper">
                        <div class="card__icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                 fill="none" class="card__icon">
                                <path
                                    d="M11.6666 11.8768V13.618C11.1453 13.4337 10.5843 13.3335 9.99992 13.3335C7.23849 13.3335 4.99992 15.5721 4.99992 18.3335H3.33325C3.33325 14.6516 6.31802 11.6668 9.99992 11.6668C10.5754 11.6668 11.1339 11.7397 11.6666 11.8768ZM9.99992 10.8335C7.23742 10.8335 4.99992 8.596 4.99992 5.8335C4.99992 3.071 7.23742 0.833496 9.99992 0.833496C12.7624 0.833496 14.9999 3.071 14.9999 5.8335C14.9999 8.596 12.7624 10.8335 9.99992 10.8335ZM9.99992 9.16683C11.8416 9.16683 13.3333 7.67516 13.3333 5.8335C13.3333 3.99183 11.8416 2.50016 9.99992 2.50016C8.15825 2.50016 6.66659 3.99183 6.66659 5.8335C6.66659 7.67516 8.15825 9.16683 9.99992 9.16683ZM14.9999 14.1668V11.6668H16.6666V14.1668H19.1666V15.8335H16.6666V18.3335H14.9999V15.8335H12.4999V14.1668H14.9999Z"
                                    fill="currentColor"/>
                            </svg>
                        </div>
                        <h2 class="card__title">
                            Ada anggota tim dari institusi lain<br>
                            yang belum memiliki akun?
                        </h2>
                    </div>
                    <a href="{{ route('litabmas.usulan-dosen-eksternal.create') }}" type="button"
                       class="btn btn_outline btn_xs">
                        Ajukan Pembuatan Akun
                    </a>
                </div>

                @if(!empty($listUndangan))
                    <div class="card__footer">
                        <p class="card__footer-text">
                            <span class="icon icon-information-circle"></span>
                            Ada <span class="card__footer-highlight">{{ count($listUndangan) }} Undangan</span> yang menunggu persetujuan Anda
                            <a href="#" data-toggle="modal" data-target="#modal-invitation-detail"
                               class="btn btn_link btn_sm">Detail</a>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- [END] Section 1 --}}
    {{-- [START] Section 2 --}}
    <div class="card card_table card_bordered">
        <div class="card__body">
            <div class="box-table">
                <div class="box-table__header">
                    <div class="card__wrapper card__wrapper_hr">
                        <div class="card__icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                 fill="none" class="card__icon">
                                <path
                                    d="M7.5 14.1667C7.5 14.1667 13.3333 15 15.8333 17.5H16.6667C17.1269 17.5 17.5 17.1269 17.5 16.6667V11.6142C18.2188 11.4292 18.75 10.7766 18.75 10C18.75 9.22342 18.2188 8.57083 17.5 8.38583V3.33333C17.5 2.8731 17.1269 2.5 16.6667 2.5H15.8333C13.3333 5 7.5 5.83333 7.5 5.83333H4.16667C3.24619 5.83333 2.5 6.57952 2.5 7.5V12.5C2.5 13.4205 3.24619 14.1667 4.16667 14.1667H5L5.83333 18.3333H7.5V14.1667ZM9.16667 7.21767C9.73608 7.0955 10.4396 6.92661 11.1994 6.70311C12.5979 6.29178 14.375 5.64385 15.8333 4.64548V15.3545C14.375 14.3562 12.5979 13.7083 11.1994 13.2969C10.4396 13.0734 9.73608 12.9045 9.16667 12.7823V7.21767ZM4.16667 7.5H7.5V12.5H4.16667V7.5Z"
                                    fill="currentColor"/>
                            </svg>
                        </div>
                        <h2 class="card__title">
                            Pendanaan Sedang Dibuka
                        </h2>
                    </div>
                </div>

                <div class="box-table__content">
                    <div class="table-max">
                        <table>
                            <thead>
                            <tr wire:ignore>
                                <th width="48">No</th>
                                <th width="78">Periode</th>
                                <th width="auto">Sumber Pendanaan</th>
                                <th width="130">Total Pendanaan</th>
                                <th width="150">Periode Pendaftaran</th>
                                <th class="cell-action cell-center">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(empty($dataPendanaan['data']->items))
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-state__illust-wrapper">
                                                <img src="{{ asset('images/dashboard-empty-state-illust.webp') }}"
                                                     alt="Empty illustrasion" class="empty-state__illust">
                                            </div>
                                            <span class="empty-state__title">Belum ada Pendanaan Kegiatan yang sedang dibuka</span>
                                            <p class="empty-state__sub-title">Saat ini, belum ada pendanaan kegiatan
                                                yang sedang dibuka. Silakan <br>cek kembali nanti untuk informasi
                                                terbaru.</p>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($dataPendanaan['data']->items as $pendanaan)
                                    <tr>
                                        <td>{{ $loop->iteration + $dataPendanaan['data']->perPage * ($dataPendanaan['data']->currentPage - 1) }}</td>
                                        <td>{{ $pendanaan['tahun_periode_pendanaan'] }}</td>
                                        <td class="cell-nowrap">{{ $pendanaan['nama_sumber_pendanaan'] }}</td>
                                        <td>
                                            <x-litabmas::fields.format_currency
                                                :value="$pendanaan['total_pendanaan']"
                                                :data="$pendanaan"
                                            />
                                        </td>
                                        <td>
                                            @if(empty($pendanaan['waktu_mulai']) && empty($pendanaan['waktu_selesai']))
                                                -
                                            @else
                                                {{ Date::formatDateRange($pendanaan['waktu_mulai'], $pendanaan['waktu_selesai']) }}
                                            @endif
                                        </td>
                                        <td class="cell-action">
                                            <div class="dropdown-group">
                                                <a href="{{ route('litabmas.pengumuman-pendanaan.show', $pendanaan['id']) }}" class="btn btn_outline btn_xs btn_icon" data-btn-label="Detail">
                                                    <span class="icon icon-eye-solid"></span>
                                                </a>
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
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- <div class="box-table__footer">
                    <x-core::table.navigation
                        gotoPage="gotoPagePendanaan"
                        nextPage="nextPagePendanaan"
                        previousPage="previousPagePendanaan"
                        setPerPage="setPerPagePendanaan"
                        :data="$dataPendanaan['data']"
                    />
                </div> --}}
            </div>
        </div>
    </div>
    {{-- [END] Section 2 --}}
    {{-- [START] Section 3 --}}
    <div class="card card_table card_bordered">
        <div class="card__body">
            <div class="box-table">
                <div class="box-table__header box-table__header_b-bordered box-table__header_y-paddingless">
                    <nav class="nav-tab  nav-tab_large nav-tab_lineless" wire:ignore>
                        <ul class="nav-tab__wrapper">
                            <li class="nav-tab__item active" wire:click="setJenisKegiatan('{{ \Modules\Litabmas\Enums\JenisPendanaanEnum::CODE_PENELITIAN }}')"
                                data-toggle="tab" data-target="#tab-pane-research-history">
                                Riwayat Penelitian
                            </li>
                            <li class="nav-tab__item" data-toggle="tab"
                                wire:click="setJenisKegiatan('{{ \Modules\Litabmas\Enums\JenisPendanaanEnum::CODE_PENGABDIAN }}')"
                                data-target="#tab-pane-community-service-history">
                                Riwayat Pengabdian Masyarakat
                            </li>
                        </ul>
                    </nav>

                    <input type="hidden" id="tab-pane-research-history"></input>
                    <input type="hidden"  id="tab-pane-community-service-history"></input>
                </div>

                <div class="box-table__header">
                    <div class="grid">
                        <div class="col-12 col-sm-4">
                            <div class="form-control">
                                <div class="form-control__group">
                                    <span data-input-icon="search"></span>
                                    <input type="search" class="form-control__input"
                                           wire:model.live.debounce.500ms="searchRiwayat"
                                           placeholder="Cari judul proposal...">
                                    <span data-clear="input"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-table__content">
                    <div class="table-max table_striped">
                        <table>
                            <thead>
                            <tr>
                                <th width="48">No</th>
                                <th width="78">Periode</th>
                                <th width="auto">Judul Proposal</th>
                                <th width="250">Klaster Pendanaan</th>
                                <th width="172">Ketua Penelitian</th>
                                <th width="196">Status Pengerjaan</th>
                                <th class="cell-action cell-center">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(empty($dataRiwayat['data']->items))
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-state__illust-wrapper">
                                                <img
                                                    src="{{ asset('images/dashboard-empty-state-illust.webp') }}"
                                                    alt="Empty illustrasion" class="empty-state__illust">
                                            </div>
                                            <span class="empty-state__title">Belum ada Proposal Penelitian yang Anda ajukan</span>
                                            <p class="empty-state__sub-title">Mari, ajukan proposal penelitian
                                                dan raih pendanaan terbaik</p>

                                            @if(auth()->user()?->kode_role !== Role::ROLE_DOSEN_EKSTERNAL)
                                                <div class="empty-state__action">
                                                    <a href="{{ route('litabmas.pengajuan-pendanaan.create') }}">
                                                        <button type="button" class="btn btn_outline btn_xs">
                                                            <span class="icon icon-plus-mini"></span>
                                                            Ajukan Proposal
                                                        </button>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($dataRiwayat['data']->items as $riwayat)
                                    <tr>
                                        <td>{{ $loop->iteration + $dataRiwayat['data']->perPage * ($dataRiwayat['data']->currentPage - 1) }}</td>
                                        <td>{{ $riwayat['tahun_periode_pendanaan'] }}</td>
                                        <td>{{ $riwayat['judul_penelitian'] }}</td>
                                        <td>{{ $riwayat['nama_klaster'] }}</td>
                                        <td>{{ $riwayat['nama_ketua'] }}</td>
                                        <td>
                                            <x-litabmas::fields.status_agenda_kegiatan :value="true" :data="$riwayat" />
                                        </td>
                                        <td class="cell-action">
                                            <div class="dropdown-group">
                                                <a href="{{ route('litabmas.pengajuan-pendanaan.show', $riwayat['id']) }}" class="btn btn_outline btn_xs btn_icon"
                                                   data-btn-label="Detail">
                                                    <span class="icon icon-eye-solid"></span>
                                                </a>
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
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-table__footer">
                    <x-core::table.navigation
                        gotoPage="gotoPageRiwayat"
                        nextPage="nextPageRiwayat"
                        previousPage="previousPageRiwayat"
                        setPerPage="setPerPageRiwayat"
                        :data="$dataRiwayat['data']"
                    />
                </div>

                <div class="box-table__footer">
                    <div class="callout">
                        <span class="callout__title">Tahapan Umum Pengajuan Pendanaan</span>
                        <div class="callout__wrapper">
                            <ol class="callout__list">
                                <li class="callout__item">Pendaftaran</li>
                                <li class="callout__item">Review Administrasi</li>
                                <li class="callout__item">Pengumuman Administrasi</li>
                                <li class="callout__item">Review Proposal</li>
                                <li class="callout__item">Pengumuman Lolos Nominasi</li>
                            </ol>
                            <ol class="callout__list" start="6">
                                <li class="callout__item">Presentasi Proposal</li>
                                <li class="callout__item">Pengumuman Lolos Pendanaan</li>
                                <li class="callout__item">Pelaksanaan Penelitian / Pengabdian</li>
                                <li class="callout__item">Pengumpulan Progress Report</li>
                                <li class="callout__item">Presentasi Progress Report</li>
                            </ol>
                            <ol class="callout__list" start="11">
                                <li class="callout__item">Pengumpulan Output</li>
                                <li class="callout__item">Presentasi Output</li>
                                <li class="callout__item">Pengumpulan Outcome</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- [END] Section 3 --}}
    {{-- [START] Section 4 --}}
    <div class="grid cols-1 cols-md-3">
        <div class="col-1 col-md-2">
            <div class="card card_table card_bordered">
                <div class="card__body">
                    <div class="box-table">
                        <div class="box-table__header box-table__header_b-bordered">
                            <div class="card__wrapper card__wrapper_hr">
                                <div class="card__icon-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                         fill="none" class="card__icon">
                                        <path
                                            d="M7.5 14.1667C7.5 14.1667 13.3333 15 15.8333 17.5H16.6667C17.1269 17.5 17.5 17.1269 17.5 16.6667V11.6142C18.2188 11.4292 18.75 10.7766 18.75 10C18.75 9.22342 18.2188 8.57083 17.5 8.38583V3.33333C17.5 2.8731 17.1269 2.5 16.6667 2.5H15.8333C13.3333 5 7.5 5.83333 7.5 5.83333H4.16667C3.24619 5.83333 2.5 6.57952 2.5 7.5V12.5C2.5 13.4205 3.24619 14.1667 4.16667 14.1667H5L5.83333 18.3333H7.5V14.1667ZM9.16667 7.21767C9.73608 7.0955 10.4396 6.92661 11.1994 6.70311C12.5979 6.29178 14.375 5.64385 15.8333 4.64548V15.3545C14.375 14.3562 12.5979 13.7083 11.1994 13.2969C10.4396 13.0734 9.73608 12.9045 9.16667 12.7823V7.21767ZM4.16667 7.5H7.5V12.5H4.16667V7.5Z"
                                            fill="currentColor"/>
                                    </svg>
                                </div>
                                <h2 class="card__title">
                                    Penilaian Reviewer
                                </h2>
                            </div>
                        </div>
                        <div class="box-table__header box-table__header_b-bordered box-table__header_y-paddingless">
                            <nav class="nav-tab  nav-tab_large nav-tab_lineless">
                                <ul class="nav-tab__wrapper">
                                    <li class="nav-tab__item active" data-toggle="tab"
                                        data-target="#tab-pane-isian_proposal"
                                        wire:click="setTabPenilaianActive('isian_proposal')"
                                        wire:ignore.self
                                    >
                                        Isian Proposal
                                        @if(!empty($countPenilaian['isian_proposal']))
                                            <span class="badge badge_danger">{{ $countPenilaian['isian_proposal'] }}</span>
                                        @endif
                                    </li>
                                    <li class="nav-tab__item" data-toggle="tab"
                                        data-target="#tab-pane-presentasi_proposal"
                                        wire:click="setTabPenilaianActive('presentasi_proposal')"
                                        wire:ignore.self
                                    >
                                        Presentasi Proposal
                                        @if(!empty($countPenilaian['presentasi_proposal']))
                                            <span class="badge badge_danger">{{ $countPenilaian['presentasi_proposal'] }}</span>
                                        @endif
                                    </li>
                                    <li class="nav-tab__item" data-toggle="tab"
                                        data-target="#tab-pane-progress_report"
                                        wire:click="setTabPenilaianActive('progress_report')"
                                        wire:ignore.self
                                    >
                                        Progress Report
                                        @if(!empty($countPenilaian['progress_report']))
                                            <span class="badge badge_danger">{{ $countPenilaian['progress_report'] }}</span>
                                        @endif
                                    </li>
                                    <li class="nav-tab__item" data-toggle="tab"
                                        data-target="#tab-pane-output"
                                        wire:click="setTabPenilaianActive('output')"
                                        wire:ignore.self
                                    >
                                        Penilaian Output
                                        @if(!empty($countPenilaian['output']))
                                            <span class="badge badge_danger">{{ $countPenilaian['output'] }}</span>
                                        @endif
                                    </li>
                                </ul>
                            </nav>
                        </div>

                        <div class="box-table__header">
                            <div class="grid">
                                <div class="col-12 col-sm-4">
                                    <div class="form-control">
                                        <div class="form-control__group">
                                            <span data-input-icon="search"></span>
                                            <input type="search" class="form-control__input"
                                                   wire:model.live.debounce.500ms="searchPenilaian"
                                                   placeholder="Cari judul proposal">
                                            <span data-clear="input"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-content">
                            @foreach($tabPenilaian as $namaPenilaian)
                                <div class="tab-pane tab-pane_paddingless" id="tab-pane-{{ $namaPenilaian }}" wire:ignore.self>
                                    <div class="box-table__content">
                                        <div class="table-max table_striped">
                                            <table>
                                                <thead>
                                                <tr>
                                                    <th width="48">No</th>
                                                    <th width="122" class="cell-sorting">ID Registrasi</th>
                                                    <th width="250">Judul Proposal</th>
                                                    <th width="142" class="cell-sorting">Tanggal Penilaian</th>
                                                    <th width="142" class="cell-sorting">Status Penilaian</th>
                                                    <th class="cell-action cell-center">Aksi</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(empty($dataPenilaian[$namaPenilaian]['data']->items))
                                                    <tr>
                                                        <td colspan="6">
                                                            <div class="empty-state">
                                                                <div class="empty-state__illust-wrapper">
                                                                    <img
                                                                        src="{{ asset('images/dashboard-empty-state-illust.webp') }}"
                                                                        alt="Empty illustrasion"
                                                                        class="empty-state__illust">
                                                                </div>
                                                                <span
                                                                    class="empty-state__title">Daftar {{ $this::LABEL_PENILAIAN_REVIEWER[$namaPenilaian] }}</span>
                                                                <p class="empty-state__sub-title">Belum terdapat {{ strtolower($this::LABEL_PENILAIAN_REVIEWER[$namaPenilaian]) }} yang perlu anda berikan penilaian</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @else
                                                    @foreach($dataPenilaian[$namaPenilaian]['data']->items as $penilaian)
                                                        <tr>
                                                            <td>{{ $loop->iteration + $dataRiwayat['data']->perPage * ($dataRiwayat['data']->currentPage - 1) }}</td>
                                                            <td>{{ $penilaian['kode_registrasi'] }}</td>
                                                            <td>{{ $penilaian['judul_penelitian'] }}</td>
                                                            <td>
                                                                {{ isset($penilaian['tanggal_mulai_penilaian']) && isset($penilaian['tanggal_selesai_penilaian'])
                                                                    ? Date::formatDateRange($penilaian['tanggal_mulai_penilaian'], $penilaian['tanggal_selesai_penilaian'])
                                                                    : ''
                                                                }}
                                                            </td>
                                                            <td>
                                                                <x-litabmas::fields.status_penilaian_progress_report :value="$penilaian['status_penilaian_'.$namaPenilaian]" />
                                                            </td>
                                                            <td class="cell-action">
                                                                <div class="dropdown-group">
                                                                    <a href="{{ route($this::VIEW_PENILAIAN_REVIEWER[$namaPenilaian], $penilaian['id']) }}" class="btn btn_outline btn_xs btn_icon"
                                                                       data-btn-label="Detail">
                                                                        <span class="icon icon-eye-solid"></span>
                                                                    </a>
                                                                    <div class="dropdown-group__target">
                                                                        <div class="dropdown-group__toggle">
                                                                            <a href="#" class="btn btn_outline btn_xs btn_icon">
                                                                            <span
                                                                                class="icon icon-ellipsis-horizontal"></span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="box-table__footer">
                                        @if(!empty($dataPenilaian[$namaPenilaian]))
                                            <x-core::table.navigation
                                                gotoPage="gotoPagePenilaian"
                                                nextPage="nextPagePenilaian"
                                                previousPage="previousPagePenilaian"
                                                setPerPage="setPerPagePenilaian"
                                                :data="$dataPenilaian[$namaPenilaian]['data']"
                                            />
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card_bordered card_overflow-hidden">
            <div class="card__header">
                <div class="card__wrapper card__wrapper_hr">
                    <div class="card__icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"
                             class="card__icon">
                            <path
                                d="M5.625 2.5V4.375M14.375 2.5V4.375M2.5 15.625V6.25C2.5 5.21447 3.33947 4.375 4.375 4.375H15.625C16.6605 4.375 17.5 5.21447 17.5 6.25V15.625M2.5 15.625C2.5 16.6605 3.33947 17.5 4.375 17.5H15.625C16.6605 17.5 17.5 16.6605 17.5 15.625M2.5 15.625V9.375C2.5 8.33947 3.33947 7.5 4.375 7.5H15.625C16.6605 7.5 17.5 8.33947 17.5 9.375V15.625M10 10.625H10.0063V10.6313H10V10.625ZM10 12.5H10.0063V12.5063H10V12.5ZM10 14.375H10.0063V14.3813H10V14.375ZM8.125 12.5H8.13125V12.5063H8.125V12.5ZM8.125 14.375H8.13125V14.3813H8.125V14.375ZM6.25 12.5H6.25625V12.5063H6.25V12.5ZM6.25 14.375H6.25625V14.3813H6.25V14.375ZM11.875 10.625H11.8813V10.6313H11.875V10.625ZM11.875 12.5H11.8813V12.5063H11.875V12.5ZM11.875 14.375H11.8813V14.3813H11.875V14.375ZM13.75 10.625H13.7563V10.6313H13.75V10.625ZM13.75 12.5H13.7563V12.5063H13.75V12.5Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h2 class="card__title">
                        Jadwal Presentasi
                    </h2>
                </div>
            </div>
            <div class="card__header">
                <div class="form-control" wire:ignore>
                    <input type="text" id="inline-calendar-pres-sched"
                           class="form-control__input form-control__input_inline-calendar" placeholder="Select date">
                </div>
            </div>
            <div class="card__body card__body_for-pres-sched">
                <div class="pres-sched-wrapper">
                    <div class="grid cols-1">
                        <nav class="nav-tab nav-tab_phill-sm nav-tab_phill-half" wire:ignore>
                            <ul class="nav-tab__wrapper">
                                <li class="nav-tab__item active" data-toggle="tab"
                                    data-target="#tab-pane-as-researcher">
                                    Sebagai Peneliti
                                </li>
                                <li class="nav-tab__item" data-toggle="tab" data-target="#tab-pane-as-reviewer">
                                    Sebagai Reviewer
                                </li>
                            </ul>
                        </nav>
                        <div class="tab-content">
                            <div class="tab-pane tab-pane_paddingless" id="tab-pane-as-researcher" wire:ignore.self>
                                <div class="schedule-wrapper" wire:loading.grid>
                                    <a href="#" class="card-sched card-sched_on-load">
                                            <span class="card-sched__title">
                                                Analisis Keamanan Sistem Informasi pada Aplikasi E-Learning di Perguruan Tinggi
                                            </span>
                                        <span class="card-sched__desc">15 Februari 2023, 08:00 WIB</span>
                                        <div class="card-sched__wrapper">
                                            <div class="card-sched__avatar-wrapper">
                                                <div class="avatar avatar_xs">
                                                    <img
                                                        src="{{ Page::quantumAsset('images/example-profile3.jpg') }}">
                                                </div>
                                                <div class="avatar avatar_xs" data-avatar-name="Sendini"></div>
                                                <div class="avatar avatar_xs" data-avatar-name="Dnaja"></div>
                                                <div class="avatar avatar_xs avatar_more"
                                                     data-avatar-name="+2"></div>
                                            </div>
                                            <span class="badge badge_outline-primary badge_sm">
                                                    Presentasi Output
                                            </span>
                                        </div>
                                    </a>
                                    <a href="#" class="card-sched card-sched_on-load">
                                            <span class="card-sched__title">
                                                Analisis Keamanan Sistem Informasi pada Aplikasi E-Learning di Perguruan Tinggi
                                            </span>
                                        <span class="card-sched__desc">15 Februari 2023, 08:00 WIB</span>
                                        <div class="card-sched__wrapper">
                                            <div class="card-sched__avatar-wrapper">
                                                <div class="avatar avatar_xs">
                                                    <img
                                                        src="{{ Page::quantumAsset('images/example-profile3.jpg') }}">
                                                </div>
                                                <div class="avatar avatar_xs" data-avatar-name="Sendini"></div>
                                                <div class="avatar avatar_xs" data-avatar-name="Dnaja"></div>
                                                <div class="avatar avatar_xs avatar_more"
                                                     data-avatar-name="+2"></div>
                                            </div>
                                            <span class="badge badge_outline-primary badge_sm">
                                                    Presentasi Output
                                            </span>
                                        </div>
                                    </a>
                                    <a href="#" class="card-sched card-sched_on-load">
                                            <span class="card-sched__title">
                                                Analisis Keamanan Sistem Informasi pada Aplikasi E-Learning di Perguruan Tinggi
                                            </span>
                                        <span class="card-sched__desc">15 Februari 2023, 08:00 WIB</span>
                                        <div class="card-sched__wrapper">
                                            <div class="card-sched__avatar-wrapper">
                                                <div class="avatar avatar_xs">
                                                    <img
                                                        src="{{ Page::quantumAsset('images/example-profile3.jpg') }}">
                                                </div>
                                                <div class="avatar avatar_xs" data-avatar-name="Sendini"></div>
                                                <div class="avatar avatar_xs" data-avatar-name="Dnaja"></div>
                                                <div class="avatar avatar_xs avatar_more"
                                                     data-avatar-name="+2"></div>
                                            </div>
                                            <span class="badge badge_outline-primary badge_sm">
                                                    Presentasi Output
                                            </span>
                                        </div>
                                    </a>
                                </div>

                                <div class="schedule-wrapper" wire:loading.remove>
                                    @php
                                        $jadwalPeneliti = array_filter(
                                            $dataJadwal[$this::TAB_JADWAL_PENELITI],
                                            fn($j) => \Carbon\Carbon::parse($j->waktu_pelaksanaan)->format('j') == $jadwalTanggal
                                        );
                                    @endphp
                                    @if(empty($jadwalPeneliti))
                                        <div class="empty-state" style="padding: 0.25rem 2rem 0 2rem;">
                                            <div class="empty-state__illust-wrapper">
                                                <img
                                                    src="{{ asset('images/dashboard-empty-state-illust.webp') }}"
                                                    alt="Empty illustrasion"
                                                    class="empty-state__illust">
                                            </div>
                                            <span
                                                class="empty-state__title">Daftar Jadwal Presentasi</span>
                                            <p class="empty-state__sub-title">Belum terdapat jadwal presentasi yang perlu anda ikuti</p>
                                        </div>
                                    @else
                                        @foreach($jadwalPeneliti as $jadwal)
                                            @php
                                                $style = $this->getJadwalCardStyle($jadwal->tipe_presentasi);
                                            @endphp
                                            <a href="{{ route('litabmas.pengajuan-pendanaan.show', $jadwal->id) }}" class="card-sched {{ $style }}">
                                                    <span class="card-sched__title">
                                                        {{ $jadwal->judul_penelitian }}
                                                    </span>
                                                <span class="card-sched__desc">{{ Date::formatDateTimeLong($jadwal->waktu_pelaksanaan) }}</span>
                                                <div class="card-sched__wrapper">
                                                    <div class="card-sched__avatar-wrapper">
                                                        @php
                                                            $anggota = explode(';', $jadwal->semua_anggota);

                                                            $firstSlice = array_slice($anggota, 0, 3);
                                                            $extraCount = count($anggota) - count($firstSlice);
                                                        @endphp

                                                        @foreach($firstSlice as $anggota)
                                                            <div class="avatar avatar_xs" data-avatar-name="{{ $anggota }}"></div>

                                                        @endforeach

                                                        @if(!empty($extraCount))
                                                            <div class="avatar avatar_xs avatar_more" data-avatar-name="+{{$extraCount}}"></div>
                                                        @endif
                                                    </div>
                                                    <span class="badge badge_outline-primary badge_sm">
                                                        {{ PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_OPTIONS[$jadwal->tipe_presentasi] }}
                                                    </span>
                                                </div>
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane tab-pane_paddingless" id="tab-pane-as-reviewer" wire:ignore.self>
                                <div class="schedule-wrapper" wire:loading.grid>
                                    <a href="#" class="card-sched card-sched_on-load">
                                            <span class="card-sched__title">
                                                Analisis Keamanan Sistem Informasi pada Aplikasi E-Learning di Perguruan Tinggi
                                            </span>
                                        <span class="card-sched__desc">15 Februari 2023, 08:00 WIB</span>
                                        <div class="card-sched__wrapper">
                                            <div class="card-sched__avatar-wrapper">
                                                <div class="avatar avatar_xs">
                                                    <img
                                                        src="{{ Page::quantumAsset('images/example-profile3.jpg') }}">
                                                </div>
                                                <div class="avatar avatar_xs" data-avatar-name="Sendini"></div>
                                                <div class="avatar avatar_xs" data-avatar-name="Dnaja"></div>
                                                <div class="avatar avatar_xs avatar_more"
                                                     data-avatar-name="+2"></div>
                                            </div>
                                            <span class="badge badge_outline-primary badge_sm">
                                                    Presentasi Output
                                            </span>
                                        </div>
                                    </a>
                                    <a href="#" class="card-sched card-sched_on-load">
                                            <span class="card-sched__title">
                                                Analisis Keamanan Sistem Informasi pada Aplikasi E-Learning di Perguruan Tinggi
                                            </span>
                                        <span class="card-sched__desc">15 Februari 2023, 08:00 WIB</span>
                                        <div class="card-sched__wrapper">
                                            <div class="card-sched__avatar-wrapper">
                                                <div class="avatar avatar_xs">
                                                    <img
                                                        src="{{ Page::quantumAsset('images/example-profile3.jpg') }}">
                                                </div>
                                                <div class="avatar avatar_xs" data-avatar-name="Sendini"></div>
                                                <div class="avatar avatar_xs" data-avatar-name="Dnaja"></div>
                                                <div class="avatar avatar_xs avatar_more"
                                                     data-avatar-name="+2"></div>
                                            </div>
                                            <span class="badge badge_outline-primary badge_sm">
                                                    Presentasi Output
                                            </span>
                                        </div>
                                    </a>
                                    <a href="#" class="card-sched card-sched_on-load">
                                            <span class="card-sched__title">
                                                Analisis Keamanan Sistem Informasi pada Aplikasi E-Learning di Perguruan Tinggi
                                            </span>
                                        <span class="card-sched__desc">15 Februari 2023, 08:00 WIB</span>
                                        <div class="card-sched__wrapper">
                                            <div class="card-sched__avatar-wrapper">
                                                <div class="avatar avatar_xs">
                                                    <img
                                                        src="{{ Page::quantumAsset('images/example-profile3.jpg') }}">
                                                </div>
                                                <div class="avatar avatar_xs" data-avatar-name="Sendini"></div>
                                                <div class="avatar avatar_xs" data-avatar-name="Dnaja"></div>
                                                <div class="avatar avatar_xs avatar_more"
                                                     data-avatar-name="+2"></div>
                                            </div>
                                            <span class="badge badge_outline-primary badge_sm">
                                                    Presentasi Output
                                            </span>
                                        </div>
                                    </a>
                                </div>

                                <div class="schedule-wrapper" wire:loading.remove>
                                    @php
                                        $jadwalReviewer = array_filter(
                                            $dataJadwal[$this::TAB_JADWAL_REVIEWER],
                                            fn($j) => \Carbon\Carbon::parse($j->waktu_pelaksanaan)->format('j') == $jadwalTanggal
                                        );
                                    @endphp
                                    @if(empty($jadwalReviewer))
                                        <div class="empty-state" style="padding: 0.25rem 2rem 0 2rem;">
                                            <div class="empty-state__illust-wrapper">
                                                <img
                                                    src="{{ asset('images/dashboard-empty-state-illust.webp') }}"
                                                    alt="Empty illustrasion"
                                                    class="empty-state__illust">
                                            </div>
                                            <span
                                                class="empty-state__title">Daftar Jadwal Presentasi</span>
                                            <p class="empty-state__sub-title">Belum terdapat jadwal reviewer yang perlu anda ikuti</p>
                                        </div>
                                    @else
                                        @foreach($jadwalReviewer as $jadwal)
                                            @php
                                                $style = $this->getJadwalCardStyle($jadwal->tipe_presentasi);
                                            @endphp
                                            <a href="{{ route('litabmas.pengajuan-pendanaan.show', $jadwal->id) }}" class="card-sched {{ $style }}">
                                                    <span class="card-sched__title">
                                                        {{ $jadwal->judul_penelitian }}
                                                    </span>
                                                <span class="card-sched__desc">{{ Date::formatDateTimeLong($jadwal->waktu_pelaksanaan) }}</span>
                                                <div class="card-sched__wrapper">
                                                    <div class="card-sched__avatar-wrapper">
                                                        @php
                                                            $anggota = explode(';', $jadwal->semua_anggota);

                                                            $firstSlice = array_slice($anggota, 0, 3);
                                                            $extraCount = count($anggota) - count($firstSlice);
                                                        @endphp

                                                        @foreach($firstSlice as $anggota)
                                                            <div class="avatar avatar_xs" data-avatar-name="{{ $anggota }}"></div>

                                                        @endforeach

                                                        @if(!empty($extraCount))
                                                            <div class="avatar avatar_xs avatar_more" data-avatar-name="+{{$extraCount}}"></div>
                                                        @endif
                                                    </div>
                                                    <span class="badge badge_outline-primary badge_sm">
                                                        {{ PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_OPTIONS[$jadwal->tipe_presentasi] }}
                                                    </span>
                                                </div>
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- [END] Section 4 --}}

    @php
        $merged = array_merge($dataJadwal[$this::TAB_JADWAL_PENELITI], $dataJadwal[$this::TAB_JADWAL_REVIEWER]);
        $data = array_map(function ($item) {
            return [
                'type' => $item->tipe_presentasi,
                'date' => \Carbon\Carbon::parse($item->waktu_pelaksanaan)->format('Y-m-d'),
            ];
        }, $merged);

        // group by type without collect
        $grouped = [];
        foreach ($data as $item) {
            $grouped[$item['type']][] = $item['date'];
        }

        $dots = [
            "#15B79E" => $grouped[PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_PROPOSAL] ?? [],
            "#0F6AF5" => $grouped[PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_PROGRESS_REPORT] ?? [],
            "#EF9600" => $grouped[PengajuanPendanaanJadwalPresentasi::TIPE_PRESENTASI_OUTPUT] ?? [],
        ];
    @endphp

    <input type="hidden" name="dots_jadwal" value="{{ json_encode($dots) }}">

    {{-- [START] Modals --}}
    <div id="modal-invitation-detail" class="modal modal_confirmation-primary modal_inv-detail">
        <div class="modal__overlay" data-dismiss="modal"></div>
        <div class="modal__wrapper">
            <div class="modal__header">
                <div class="modal__header-wrapper">
                    <h3 class="modal__title">Menunggu Persetujuan Anda</h3>
                </div>
                <span class="icon icon-x-mark-mini" data-dismiss="modal"></span>
            </div>
            <div class="modal__body">
                @if(count($listUndangan) === 0)
                    <div class="empty-state empty-state_inv-detail">
                        <div class="empty-state__illust-wrapper">
                            <img src="{{ asset('images/dashboard-empty-state-illust.webp') }}" alt="Empty illustrasion"
                                 class="empty-state__illust">
                        </div>
                        <span class="empty-state__title">Belum Ada Undangan Kolaborasi</span>
                        <p class="empty-state__sub-title">Undangan kolaborasi dari peneliti lain akan ditampilkan disini
                            saat sudah tersedia</p>
                    </div>
                @else
                    @foreach($listUndangan as $undangan)
                        <div class="inv-item">
                            <div class="avatar">
                                <p class="avatar__acronym">
                                    {{ substr($undangan->nama_ketua, 0, 2) }}
                                </p>
                            </div>
                            <div class="inv-item__wrapper">
                                <h2 class="inv-item__title">{{ $undangan->nama_ketua }} Mengundang Anda</h2>
                                <p class="inv-item__desc">Proposal: {{ $undangan->judul_penelitian }}</p>
                                <span class="inv-item__label">{{ \Carbon\Carbon::parse($undangan->waktu_dibuat)->diffForHumans() }}</span>
                            </div>
                            <div class="inv-item__action">
                                <a href="{{ route('litabmas.pengajuan-pendanaan.show', $undangan->id_pengajuan_pendanaan) }}" type="button" class="btn btn_icon btn_outline btn_xs">
                                    <span class="icon icon-eye"></span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="modal__footer">
                <div class="grid cols-1">
                    <button class="btn btn_outline" data-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-panduan" class="modal modal_confirmation-primary">
        <div class="modal__overlay" data-dismiss="modal"></div>
        <div class="modal__wrapper">
            <div class="modal__header">
                <div class="modal__header-wrapper">
                    <h3 class="modal__title">Panduan Pengajuan Aktif</h3>
                </div>
                <span class="icon icon-x-mark-mini" data-dismiss="modal"></span>
            </div>
            <div class="modal__body" style="display: flex; flex-direction: column; gap: 1rem;">
                @if(count($listUrlPanduan) === 0)
                    <div class="empty-state empty-state_inv-detail">
                        <div class="empty-state__illust-wrapper">
                            <img src="{{ asset('images/dashboard-empty-state-illust.webp') }}" alt="Empty illustrasion"
                                 class="empty-state__illust">
                        </div>
                        <span class="empty-state__title">Belum Ada Panduan</span>
                        <p class="empty-state__sub-title">Panduan aktif akan tampil disini</p>
                    </div>
                @else
                    @foreach($listUrlPanduan as $name => $url)
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 2rem;">
                            <p>{{ $name }}</p>
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">
                                <x-core::button size="xs">Buka</x-core::button>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="modal__footer">
                <div class="grid cols-1">
                    <button class="btn btn_outline" data-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- [END] Modals --}}

    @pushonce('scripts')
        <script>
            function openPanduan() {
                const urls = @js($listUrlPanduan);

                urls.forEach((url) => window.open(url, '_blank', 'noreferrer noopener'));
            }

            document.addEventListener('DOMContentLoaded', () => {
                const scrollContainer = document.querySelector('.pres-sched-wrapper');

                const checkScrollShadows = () => {
                    if (scrollContainer.scrollTop > 0) {
                        scrollContainer.parentNode.classList.add('card__body_shadow-top');
                    } else {
                        scrollContainer.parentNode.classList.remove('card__body_shadow-top');
                    }

                    if (scrollContainer.scrollHeight - scrollContainer.scrollTop > scrollContainer.clientHeight + 1) {
                        scrollContainer.parentNode.classList.add('card__body_shadow-bottom');
                    } else {
                        scrollContainer.parentNode.classList.remove('card__body_shadow-bottom');
                    }
                };
                checkScrollShadows();
                scrollContainer.addEventListener('scroll', checkScrollShadows);
            });
        </script>
    @endpushonce

    @script
        <script>
            flatpickr.localize(flatpickr.l10ns.id);

            let dots = getDots();

            const debounce = (func, wait) => {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        timeout = null;
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            };

            const update = async (selectedDates, __, instance) => {
                await $wire.setJadwalDate(instance.currentMonth + 1, instance.currentYear, selectedDates[0].getDate());

                dots = getDots();
                instance.redraw();
            }

            const updateDebounced = debounce(update, 300);

            const inlineCalendarPresSched = flatpickr(document.getElementById('inline-calendar-pres-sched'), {
                disableMobile: "true",
                defaultDate: Date.now(),
                inline: true,
                onDayCreate: function (dObj, dStr, fp, dayElem) {
                    const date = dayElem.dateObj;
                    const yearMonth = date.getFullYear() + "-" + ('0' + (date.getMonth() + 1)).slice(-2);
                    const value = yearMonth + "-" + ('0' + date.getDate()).slice(-2);

                    const divQnDots = document.createElement('div');
                    divQnDots.classList.add('flatpickr-qn-dots');
                    if (dots !== undefined) {
                        Object.keys(dots).forEach((color) => {
                            if (dots[color].includes(value.toString())) {
                                divQnDots.innerHTML += `<span class="flatpickr-qn-dot" style="background-color: ${color}"><span>`;
                            }
                        });
                    }
                    dayElem.append(divQnDots);
                },
                onChange: updateDebounced,
                onMonthChange: updateDebounced,
                onYearChange: updateDebounced
            });

            function getDots() {
                return JSON.parse(document.querySelector('[name="dots_jadwal"]').value);
            }
        </script>
    @endscript
    {{-- [END] Sections --}}
</div>
