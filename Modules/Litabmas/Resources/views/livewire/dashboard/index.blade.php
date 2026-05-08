@push('head')
    <style>
        .choices__list--dropdown,
        .choices__list[aria-expanded] {
            z-index: 1000 !important;
        }

        .choices__list--single .choices__item {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .choices__list--dropdown .choices__item.choices__placeholder {
            opacity: 1 !important;
            color: inherit !important;
        }
    </style>
@endpush
@php
    use Modules\Litabmas\Models\PengajuanPendanaanStatus2;

    $jenisPendanaanCaption = !empty($filterJenisPendanaan) ? $filterOptions['jenis_pendanaan'][$filterJenisPendanaan] : 'Semua';
    $tanggalMulaiPeriode = $periodePendanaan['tanggal_mulai'] ?? '';
    $tanggalAkhirPeriode = $periodePendanaan['tanggal_akhir'] ?? '';
@endphp

<div class="container">
    <div class="grid" style="margin-top: -4rem">
        @pushOnce('head')
            @vite('Modules/Litabmas/Resources/assets/sass/dashboard/pimpinan.scss')
            @vite('resources/js/bootstrap.tooltip.js')
            @vite('resources/scss/bootstrap.tooltip.scss')
            @vite('Modules/Litabmas/Resources/assets/sass/bootstrap/tooltip.scss')
        @endPushOnce
        <div class="col-12 grid">
            <div class="col-12 col-lg-5">
                <h1 class="main__title">Beranda</h1>
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <div class="form-control">
                    <x-core::select id="select-dropdown-page-period" data-search-placeholder="Cari..."
                        :options="$filterOptions['periode_pendanaan']" wire:model.change="filterPeriode"
                        :selected="$filterPeriode" />
                </div>
            </div>
            <div class="col-12 col-sm-4 col-lg-3">
                <div class="form-control">
                    <x-core::select id="select-dropdown-page-type-activity" data-search-placeholder="Cari..."
                        :options="$filterOptions['jenis_pendanaan']" wire:model.change="filterJenisPendanaan"
                        :selected="$filterJenisPendanaan" />
                </div>
            </div>
            <div class="col-12 col-sm-4 col-lg-2">
                <div class="form-control">
                    <x-core::select id="select-dropdown-page-unit" data-search-placeholder="Cari..."
                        class="select-search" :options="$filterOptions['unit']" wire:model.change="filterUnit"
                        :selected="$filterUnit" />
                </div>
            </div>
        </div>
        <div class="col-12" wire:ignore>
            <nav-widget module-code="litabmas"></nav-widget>
        </div>
        <div class="col-12 grid">
            <div class="col-12 col-md-5">
                <div class="card card_dashlead">
                    <div class="card__header">
                        <div class="card__header-wrap">
                            <h2 class="card__title">Ringkasan Pendanaan {{$jenisPendanaanCaption}}</h2>
                            @if(!empty($tanggalMulaiPeriode))
                                <span class="card__subtitle"><x-litabmas::fields.date :value="$tanggalMulaiPeriode" /> -
                                    <x-litabmas::fields.date :value="$tanggalAkhirPeriode" /></span>
                            @endif
                        </div>
                        {{-- <div class="card__header-wrap">
                            <button type="button" class="btn btn_link btn_xs">
                                Lihat Semua
                            </button>
                        </div> --}}
                    </div>
                    <div class="card__body">
                        <div class="grid cols-1">
                            <div class="gauge">
                                <div class="gauge__wrap">
                                    <div class="gauge__area">
                                        <canvas id="chart-gauge-bar-doughnut" height=280></canvas>
                                    </div>
                                    <div class="gauge__text">
                                        <span class="gauge__label">Total Sumber Pendanaan</span>
                                        <span
                                            class="gauge__value">Rp{{ Format::numberAbbv($sumberPendanaan['total']['maksimal_anggaran_klaster']) }}</span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="gauge-desc">
                                <div class="gauge-desc__wrap">
                                    <span class="gauge-desc__label">
                                        <span class="gauge-desc__badge" style="color: #2486FF;"></span>
                                        Dana Diberikan
                                    </span>
                                    <span
                                        class="gauge-desc__value">Rp{{ Format::numberAbbv($sumberPendanaan['total']['nominal_anggaran_disetujui']) }}</span>
                                </div>
                                <hr class="gauge-desc__line">
                                <div class="gauge-desc__wrap">
                                    <span class="gauge-desc__label">
                                        <span class="gauge-desc__badge" style="color: #EEF2F6"></span>
                                        Dana Tersisa
                                    </span>
                                    <span
                                        class="gauge-desc__value">Rp{{ Format::numberAbbv($sumberPendanaan['total']['anggaran_tersisa']) }}</span>
                                </div>
                            </div>
                            <hr>
                            @if($sumberPendanaan['total']['anggaran_tersisa'] == 0)
                                <div class="alert alert_simple alert_danger"
                                    style="--qn-alert-icon-color: var(--qn-primary-400)">
                                    <div class="alert__content">
                                        <p>
                                            Total dana diberikan adalah
                                            Rp{{ Format::numberAbbv($sumberPendanaan['total']['nominal_anggaran_disetujui']) }}
                                            untuk mendanai {{$sumberPendanaan['total']['jumlah_proposal']}} proposal,
                                            menghasilkan {{$statusOutputProposal['total_luaran_disetujui']}} luaran dan
                                            {{$statusOutcomeProposal['total_outcome_terkumpul']}} outcome. Pastikan dana
                                            yang digunakan mendapatkan hasil yang optimal.
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert_simple alert_danger">
                                    <div class="alert__content">
                                        <p>
                                            Dana tersisa sebesar
                                            Rp{{ Format::numberAbbv($sumberPendanaan['total']['anggaran_tersisa']) }}.
                                            Pertimbangkan untuk mengoptimalkan
                                            anggaran ini dengan membuka klaster pendanaan baru atau
                                            mengalokasikannya ke penelitian yang membutuhkan dana tambahan.
                                        </p>
                                    </div>

                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-7">
                <div class="card card_dashlead card_dashlead-scrollable">
                    <div class="card__header">
                        <div class="card__header-wrap">
                            <h2 class="card__title">Sumber Pendanaan {{$jenisPendanaanCaption}}</h2>
                            @if(!empty($tanggalMulaiPeriode))
                                <span class="card__subtitle"><x-litabmas::fields.date :value="$tanggalMulaiPeriode" /> -
                                    <x-litabmas::fields.date :value="$tanggalAkhirPeriode" /></span>
                            @endif
                        </div>
                        <div class="card__header-wrap">
                            <a href="{{ url('litabmas/sumber-pendanaan') }}">
                                <button type="button" class="btn btn_link btn_xs">
                                    Lihat Semua
                                </button>
                            </a>
                        </div>
                    </div>
                    @if (!empty($sumberPendanaan['data']))
                        <div class="card__outer">
                            <div class="card__body">
                                <div class="card__body-wrapper">
                                    @foreach($sumberPendanaan['data'] as $key => $item)
                                        <div class="source-item">
                                            <div class="source-item__header">
                                                <span class="source-item__title">
                                                    {{$item['nama_sumber_pendanaan']}}
                                                    <a href="{{url('litabmas/sumber-pendanaan/' . $key)}}" class="btn btn_link"
                                                        target="_blank"
                                                        aria-label="Halaman detail {{$item['nama_sumber_pendanaan']}}"><span
                                                            class="icon icon-arrow-top-right-on-square-solid"></span></a>
                                                </span>
                                                @php
                                                    $totalAnggaran = Format::numberAbbv($item['maksimal_anggaran_klaster']);
                                                    $danaDiberikan = Format::numberAbbv($item['nominal_anggaran_disetujui']);
                                                    $danaTersisa = Format::numberAbbv($item['anggaran_tersisa']);
                                                    $danaDiberikanPersentase = $item['nominal_anggaran_disetujui'] / $item['maksimal_anggaran_klaster'] * 100;
                                                @endphp
                                                <span class="source-item__desc">Rp{{$danaDiberikan}} dari total
                                                    Rp{{ $totalAnggaran }}</span>
                                            </div>
                                            <div class="progress" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="
                                                                <div class='tooltip-with-table'>
                                                                    <span class='tooltip-title'>{{$item['nama_sumber_pendanaan']}}</span>
                                                                    <ul class='tooltip-table'>
                                                                        <li>
                                                                            <span class='tooltip-table-title'>Total Pendanaan</span>
                                                                            <span class='tooltip-table-value'>: Rp{{$totalAnggaran}}</span>
                                                                        </li>
                                                                        <li>
                                                                            <span class='tooltip-table-title'>Dana Diberikan</span>
                                                                            <span class='tooltip-table-value'>: Rp{{$danaDiberikan}}</span>
                                                                        </li>
                                                                        <li>
                                                                            <span class='tooltip-table-title'>Dana Tersisa</span>
                                                                            <span class='tooltip-table-value'>: Rp{{$danaTersisa}}</span>
                                                                        </li>
                                                                        <li>
                                                                            <span class='tooltip-table-title'>Proposal Diterima</span>
                                                                            <span class='tooltip-table-value'>: {{$item['jumlah_proposal']}} Proposal</span>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            ">
                                                <div class="progress__bar" data-percent-start="0"
                                                    data-percent-end="{{$danaDiberikanPersentase}}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card__outer">
                            <div class="card__body">
                                <div class="card__body-wrapper">
                                    {{-- Empty ist --}}
                                    <div class="empty-list" style="padding: 1rem;">
                                        <div class="empty-list__wrapper">
                                            <div class="empty-list__content" style="align-items:center;">
                                                <img src="{{asset('/images/empty-state.png')}}" width="160px"
                                                    alt="illustration">
                                                <h1>Daftar Sumber Pendanaan</h1>
                                                <p style="max-width: 550px;">
                                                    Belum terdapat Sumber Pendanaan yang dibuka
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-12">
                <div class="card card_dashlead">
                    <div class="card__header">
                        <div class="card__header-wrap">
                            <h2 class="card__title">Sebaran Status Proposal {{$jenisPendanaanCaption}}</h2>
                            @if(!empty($tanggalMulaiPeriode))
                                <span class="card__subtitle"><x-litabmas::fields.date :value="$tanggalMulaiPeriode" /> -
                                    <x-litabmas::fields.date :value="$tanggalAkhirPeriode" /></span>
                            @endif
                        </div>
                        {{-- <div class="card__header-wrap">
                            <button type="button" class="btn btn_link btn_xs">
                                Lihat Semua
                            </button>
                        </div> --}}
                    </div>
                    <div class="card__body" @if (empty($sebaranStatusProposal)) style="display:none" @endif>
                        <div class="grid cols-1">
                            <div class="line-area">
                                <div class="line-area__wrap">
                                    <div class="line-area__legend">
                                        <div class="line-area__item">
                                            <span class="line-area__label">
                                                Pengajuan Proposal
                                                <span class="icon icon-information-circle" data-bs-toggle="tooltip"
                                                    data-bs-html="true" data-bs-title="
                                                            <div class='tooltip-static'>
                                                                Jumlah proposal penelitian yang diajukan oleh peneliti untuk mengikuti program pendanaan penelitian pada periode ini
                                                            </div>
                                                        ">
                                                </span>
                                            </span>
                                            <span
                                                class="line-area__value">{{$sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN]['jumlah'] ?? 0}}
                                                Proposal</span>
                                        </div>
                                        <div class="line-area__item">
                                            <span class="line-area__label">
                                                Lolos Administrasi
                                                <span class="icon icon-information-circle" data-bs-toggle="tooltip"
                                                    data-bs-html="true" data-bs-title="
                                                            <div class='tooltip-static'>
                                                                Proposal yang telah melalui tahap seleksi administrasi dan memenuhi persyaratan awal untuk lanjut ke tahap evaluasi berikutnya
                                                            </div>
                                                        ">
                                                </span>
                                            </span>
                                            <span
                                                class="line-area__value">{{$sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI]['jumlah'] ?? 0}}
                                                Proposal</span>
                                        </div>
                                        <div class="line-area__item">
                                            <span class="line-area__label">
                                                Lolos Pendanaan
                                                <span class="icon icon-information-circle" data-bs-toggle="tooltip"
                                                    data-bs-html="true" data-bs-title="
                                                            <div class='tooltip-static'>
                                                                Proposal yang berhasil mendapatkan pendanaan setelah melalui proses evaluasi dan dinilai layak untuk didanai
                                                            </div>
                                                        ">
                                                </span>
                                            </span>
                                            <span
                                                class="line-area__value">{{$sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN]['jumlah'] ?? 0}}
                                                Proposal</span>
                                        </div>
                                        <div class="line-area__item">
                                            <span class="line-area__label">
                                                Pengumpulan Luaran
                                                <span class="icon icon-information-circle" data-bs-toggle="tooltip"
                                                    data-bs-html="true" data-bs-title="
                                                            <div class='tooltip-static'>
                                                                Jumlah proposal yang telah mengumpulkan luaran penelitian atau pengabdian yang disetujui dan diakui oleh reviewer sesuai dengan kesepakatan dalam proposal
                                                            </div>
                                                        ">
                                                </span>
                                            </span>
                                            <span
                                                class="line-area__value">{{$statusOutputProposal['proposal_dengan_luaran_disetujui_minimal_1'] ?? 0}}
                                                Proposal</span>
                                        </div>
                                        <div class="line-area__item">
                                            <span class="line-area__label">
                                                Selesai Penelitian/Pengabdian
                                                <span class="icon icon-information-circle" data-bs-toggle="tooltip"
                                                    data-bs-html="true" data-bs-title="
                                                            <div class='tooltip-static'>
                                                                Jumlah proposal penelitian atau pengabdian yang telah menyelesaikan seluruh tahapan kegiatan dengan publikasi atau luaran akhir yang telah terkumpul
                                                            </div>
                                                        ">
                                                </span>
                                            </span>
                                            <span
                                                class="line-area__value">{{$sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL12_PENGUMPULAN_HASIL]['jumlah'] ?? 0}}
                                                Proposal</span>
                                        </div>
                                    </div>
                                    <div class="line-area__chart">
                                        <canvas id="chart-line-filled" height="364"
                                            style="height: 364px; max-height: 364px;"></canvas>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            @php
                                $selisihProposal = ($sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL3_DIAJUKAN]['jumlah'] ?? 0) - ($sebaranStatusProposal[PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI]['jumlah'] ?? 0);
                            @endphp
                            @if ($selisihProposal > 0)
                                <div class="alert alert_simple alert_danger">
                                    <div class="alert__content">
                                        <p>
                                            Terdapat <b>{{$selisihProposal}} Pengajuan Proposal</b> yang perlu dilakukan
                                            proses seleksi
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card__body" @if (!empty($sebaranStatusProposal)) style="display:none" @endif>
                        {{-- Empty ist --}}
                        <div class="empty-list" style="padding: 5rem 1rem;">
                            <div class="empty-list__wrapper">
                                <div class="empty-list__content" style="align-items:center;">
                                    <img src="{{asset('/images/empty-state.png')}}" width="160px" alt="illustration">
                                    <h1>Daftar Sebaran Status Proposal {{$jenisPendanaanCaption}}</h1>
                                    <p style="max-width: 550px;">
                                        Belum terdapat sebaran status proposal {{$jenisPendanaanCaption}} yang dapat
                                        ditampilkan
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card card_dashlead">
                    <div class="card__header">
                        <div class="card__header-wrap">
                            <h2 class="card__title">Hasil Luaran pada Proposal {{$jenisPendanaanCaption}}</h2>
                            @if(!empty($tanggalMulaiPeriode))
                                <span class="card__subtitle"><x-litabmas::fields.date :value="$tanggalMulaiPeriode" /> -
                                    <x-litabmas::fields.date :value="$tanggalAkhirPeriode" /></span>
                            @endif
                        </div>
                        {{-- <div class="card__header-wrap">
                            <button type="button" class="btn btn_link btn_xs">
                                Lihat Semua
                            </button>
                        </div> --}}
                    </div>
                    @if (!empty($statusOutputProposal['luaran']))
                        <div class="card__body">
                            <div class="info">
                                <div class="value">
                                    <div class="card__subtitle">
                                        Proposal dengan
                                        <br />
                                        Luaran
                                    </div>
                                    <div class="base">
                                        <div class="total">{{$statusOutputProposal['proposal_dengan_luaran']}}</div>
                                    </div>
                                </div>
                                <div class="divider"></div>
                                <div class="value">
                                    <div class="card__subtitle">
                                        Proposal belum
                                        <br />
                                        mengumpulkan luaran
                                    </div>
                                    <div class="base">
                                        <div class="total-danger">
                                            {{$statusOutputProposal['proposal_belum_mengumpulkan_luaran']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="divider"></div>
                                <div class="value2">
                                    <div class="card__subtitle">
                                        Total Luaran
                                        <br />
                                        Disetujui
                                    </div>
                                    <div class="total">{{$statusOutputProposal['total_luaran_disetujui']}}</div>
                                </div>
                                <div class="divider"></div>
                                <div class="value2">
                                    <div class="card__subtitle">
                                        Total Luaran
                                        <br />
                                        Belum Disetujui
                                    </div>
                                    <div class="total">{{$statusOutputProposal['total_luaran_belum_disetujui']}}</div>
                                </div>
                            </div>
                            <div class="table-max table-max_absolute">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Luaran
                                            </th>
                                            <th>Disetujui</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $no = 1;
                                            $jumlahTotalLuaran = 0;
                                        @endphp
                                        @foreach($statusOutputProposal['luaran'] as $idJenisLuaran => $jumlahLuaran)
                                            <tr>
                                                <td class="cell-check cell-center">
                                                    {{$no++}}
                                                </td>
                                                <td>
                                                    {{$jenisOutput[$idJenisLuaran]}}
                                                </td>
                                                <td style="  text-align: right;">
                                                    {{$jumlahLuaran}}
                                                </td>
                                            </tr>
                                            @php
                                                $jumlahTotalLuaran += $jumlahLuaran;
                                            @endphp
                                        @endforeach

                                        <tr style="background-color: #F8FAFC;">
                                            <td colspan="2">
                                                Total Keseluruhan
                                            </td>
                                            <td style="  text-align: right;">
                                                {{$jumlahTotalLuaran}}
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                            <hr style="margin-top: 1.25rem">
                            <div class="alert alert_simple alert_danger" style="padding-top: 1rem">
                                <div class="alert__content">
                                    <p>
                                        Terdapat <b>{{$statusOutputProposal['proposal_belum_mengumpulkan_luaran']}}
                                            Proposal</b> yang belum mengumpulkan luaran,
                                        pastikan seluruh luaran pada proposal telah dikumpulkan
                                        dan disetujui oleh reviewer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card__body">
                            {{-- Empty ist --}}
                            <div class="empty-list" style="padding: 5rem 1rem 10rem;">
                                <div class="empty-list__wrapper">
                                    <div class="empty-list__content" style="align-items:center;">
                                        <img src="{{asset('/images/empty-state.png')}}" width="160px" alt="illustration">
                                        <h1>Daftar Hasil Luaran</h1>
                                        <p style="max-width: 550px;">
                                            Belum terdapat hasil luaran kegiatan {{$jenisPendanaanCaption}} yang dapat
                                            ditampilkan
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card card_dashlead">
                    <div class="card__header">
                        <div class="card__header-wrap">
                            <h2 class="card__title">Hasil Publikasi pada Outcome {{$jenisPendanaanCaption}}</h2>
                            @if(!empty($tanggalMulaiPeriode))
                                <span class="card__subtitle"><x-litabmas::fields.date :value="$tanggalMulaiPeriode" /> -
                                    <x-litabmas::fields.date :value="$tanggalAkhirPeriode" /></span>
                            @endif
                        </div>
                        {{-- <div class="card__header-wrap">
                            <button type="button" class="btn btn_link btn_xs">
                                Lihat Semua
                            </button>
                        </div> --}}
                    </div>
                    @if (!empty($statusOutcomeProposal['outcome']))
                        <div class="card__body">
                            <div class="info">
                                <div class="value">
                                    <div class="card__subtitle">
                                        Proposal dengan
                                        <br />
                                        Publikasi
                                    </div>
                                    <div class="base">
                                        <div class="total">{{$statusOutcomeProposal['proposal_dengan_publikasi']}}</div>
                                    </div>
                                </div>
                                <div class="divider"></div>
                                <div class="value">
                                    <div class="card__subtitle">
                                        Proposal belum
                                        <br />
                                        publikasi
                                    </div>
                                    <div class="base">
                                        <div class="total-danger">{{$statusOutcomeProposal['proposal_belum_publikasi']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="divider"></div>
                                <div class="value2">
                                    <div class="card__subtitle">
                                        Total Outcome
                                        <br />
                                        Terkumpul
                                    </div>
                                    <div class="total">{{$statusOutcomeProposal['total_outcome_terkumpul']}}</div>
                                </div>
                                <div class="divider"></div>
                                <div class="value2">
                                    <div class="card__subtitle">
                                        Total Outcome
                                        <br />
                                        Belum Terkumpul
                                    </div>
                                    <div class="total">{{$statusOutcomeProposal['total_outcome_belum_terkumpul']}}</div>
                                </div>
                            </div>
                            <div class="table-max table-max_absolute">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Outcome
                                            </th>
                                            <th>Dipublikasikan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $no = 1;
                                            $jumlahTotalOutcome = 0;
                                        @endphp
                                        @foreach($statusOutcomeProposal['outcome'] as $idJenisOutcome => $jumlahOutcome)
                                            <tr>
                                                <td class="cell-check cell-center">
                                                    {{$no++}}
                                                </td>
                                                <td>
                                                    {{$jenisOutcome[$idJenisOutcome]}}
                                                </td>
                                                <td style="  text-align: right;">
                                                    {{$jumlahOutcome}}
                                                </td>
                                            </tr>
                                            @php
                                                $jumlahTotalOutcome += $jumlahOutcome;
                                            @endphp
                                        @endforeach
                                        <tr style="background-color: #F8FAFC;">
                                            <td colspan="2">
                                                Total Keseluruhan
                                            </td>
                                            <td style="  text-align: right;">
                                                {{$jumlahTotalOutcome}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <hr style="margin-top: 1.25rem">
                            <div class="alert alert_simple alert_danger" style="padding-top: 1rem">
                                <div class="alert__content">
                                    <p>
                                        Terdapat <b>{{$statusOutcomeProposal['proposal_belum_publikasi']}} Proposal</b> yang
                                        belum publikasi outcome,
                                        pastikan seluruh outcome pada proposal telah dipublikasi
                                        oleh peneliti sesuai dengan batas waktu yang telah
                                        ditentukan dan 1 proposal bisa lebih dari 1 publikasi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card__body">
                            {{-- Empty ist --}}
                            <div class="empty-list" style="padding: 5rem 1rem 10rem;">
                                <div class="empty-list__wrapper">
                                    <div class="empty-list__content" style="align-items:center;">
                                        <img src="{{asset('/images/empty-state.png')}}" width="160px" alt="illustration">
                                        <h1>Daftar Hasil Outcome</h1>
                                        <p style="max-width: 550px;">
                                            Belum terdapat hasil outcome kegiatan {{$jenisPendanaanCaption}} yang dapat
                                            ditampilkan
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="text/javascript"
            src="{{ Page::quantumAsset('js/vendors/chart.js-4.3.0/dist/chart.umd.js') }}"></script>
        <script type="text/javascript"
            src="{{ Page::quantumAsset('js/vendors/chartjs-plugin-datalabels-2.2.0/dist/chartjs-plugin-datalabels.min.js') }}"></script>
        <script type="text/javascript" src="{{ Page::quantumAsset('js/utils/chart-settings.js') }}"></script>
        <script>

            // Card Dashlead Scrollable Effect
            const cardDashleadScrollables = document.querySelectorAll(".card_dashlead-scrollable");
            cardDashleadScrollables.forEach(cardDashleadScrollable => {
                const cardBody = cardDashleadScrollable.querySelector(".card__body");
                const cardBodyWrapper = cardDashleadScrollable.querySelector(".card__body-wrapper");
                const setShadow = () => {
                    if (cardBody.scrollTop > 0) {
                        cardDashleadScrollable.classList.add("card_dashlead-scrollable-shadow-top");
                    } else {
                        cardDashleadScrollable.classList.remove("card_dashlead-scrollable-shadow-top");
                    }
                    if (cardBody.scrollTop + cardBody.clientHeight < cardBody.scrollHeight) {
                        cardDashleadScrollable.classList.add("card_dashlead-scrollable-shadow-bottom");
                    } else {
                        cardDashleadScrollable.classList.remove("card_dashlead-scrollable-shadow-bottom");
                    }
                }
                setShadow();
                cardBody.addEventListener("scroll", setShadow);
            });

            const buildChartDoughnut = (param) => {
                // Functional Color
                const qnFuncColorBlue = "#0F6AF5";
                const qnFuncColorNeutral200 = "#EEF2F6";

                // CHART JS 1 - Gauge Bar using Doughnut
                let don = new Chart(document.getElementById("chart-gauge-bar-doughnut"), {
                    type: "doughnut",
                    data: {
                        datasets: [
                            {
                                data: [param, 100],
                                backgroundColor: [qnFuncColorBlue, qnFuncColorNeutral200],
                            },
                        ],
                    },
                    options: {
                        // [START] options for this chart
                        cutout: "80%",
                        tooltip: false,
                        borderRadius: 0,
                        layout: {
                            padding: 0,
                        },
                        rotation: 270,
                        hoverBorderWidth: 0,
                        plugins: {
                            tooltip: false,
                        },
                        animation: false,
                        // [END] options for this chart
                    },
                });
                return don;
            }

            const buildChartLine = (param) => {
                // CHART JS 2 - Line Filled
                // data yang ingin dimasukkan ke Chart Js 5 pastikan mengikuti format berikut
                let dt = {
                    // Key yang wajib ada di dalam data
                    borderColor: "#2C7BE7", // Masukkan "transparent" jika tidak ingin memakai border color
                    datasets: [
                        // Banyak dataset bisa menyesuaikan kebutuhan
                        {
                            // Key yang wajib ada di dalam dataset
                            label: "",
                            data: param[0],
                            backgroundColor: "#2486FF",
                        },
                        {
                            label: "",
                            data: param[1],
                            backgroundColor: "#509EFF",
                        },
                        {
                            label: "",
                            data: param[2],
                            backgroundColor: "#7CB6FF",
                        },
                        {
                            label: "",
                            data: param[3],
                            backgroundColor: "#A7CFFF",
                        },
                        {
                            label: "",
                            data: param[4],
                            backgroundColor: "#CCE3FF",
                        },
                    ],
                };
                dt = qnChartFunctionLineFilledConverter(dt, true, 364 / 236); // Ratio yang dimasukkan adalah 'panjang chart + extra space' / 'panjang chart'
                let lineChart = new Chart(document.getElementById("chart-line-filled"), {
                    type: "line",
                    data: {
                        labels: dt.labels,
                        datasets: dt.datasets,
                    },
                    options: qnChartConfigLineFilledOptions,
                });

                return lineChart;
            }

            let chartDoughnut = buildChartDoughnut({{$chartData['doughnut']}});
            let chartLine = buildChartLine({{json_encode($chartData['line'])}});

            chartLine.update('none');
            chartDoughnut.update('none');

            window.addEventListener('DOMContentLoaded', (event) => {
                Livewire.on('finishLoadDataPimpinan', ([chartData]) => {
                    if (chartDoughnut && chartDoughnut.destroy) {
                        chartDoughnut.destroy();
                    }
                    if (chartLine && chartLine.destroy) {
                        chartLine.destroy();
                    }
                    chartDoughnut = buildChartDoughnut(chartData['doughnut']);
                    chartLine = buildChartLine(chartData['line']);
                });
            });
        </script>
    @endPushOnce
</div>