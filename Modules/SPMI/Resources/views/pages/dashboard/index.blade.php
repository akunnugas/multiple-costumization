@push('head')
    @vite('Modules/SPMI/Resources/assets/sass/dashboard/index.scss')
    <script type="text/javascript" src="{{ Page::quantumAsset('js/vendors/chart.js-4.3.0/dist/chart.umd.js') }}"></script>
    <script type="text/javascript"
        src="{{ Page::quantumAsset('js/vendors/chartjs-plugin-datalabels-2.2.0/dist/chartjs-plugin-datalabels.min.js') }}">
    </script>
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
    </style>
@endpush

<div class="wrapper">
    @if ($isActivePeriod)
        <div class="information">
            <div class="information__period">
                <span class="icon icon-information-circle-solid"></span>
                <p>Periode Berlangsung</p>
            </div>
            <div class="information__text">
                <p>Data yang sedang ditampilkan dapat berubah sewaktu - waktu karena sedang proses penilaian</p>
            </div>
            <div class="information__end"></div>
        </div>
    @endif
    <div class="container">
        <div class="grid">
            <div class="col-12">
                <div class="dashboard-header">
                    <div class="dashboard-header__title">
                        <span>Dashboard SPMI</span>
                    </div>
                    <div class="dashboard-header__filter">
                        <div class="grid">
                            <div class="col-12 col-sm-5">
                                <x-core::select id="filter-period" class="filter" name="filter-period"
                                    label="Pilih Periode AMI" :options="$filterOptions['audit_periode']" wire:model="filterPeriod"
                                    :selected="$filterPeriod" />
                            </div>
                            <div class="col-12 col-sm-7">
                                <x-core::select variant="search" id="filter-study-program" class="filter"
                                    name="filter-study-program" label="Pilih Unit Kerja" :options="$filterOptions['study_programs']"
                                    wire:model="filterStudyProgram" :selected="$filterStudyProgram ?? 'all'" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12" wire:ignore>
                <nav-widget module-code="spmi"></nav-widget>
            </div>
            <div class="col-12">
                <div class="grid">
                    <div class="col-12 col-md-8" wire:ignore.self>
                        <div class="card">
                            <div class="loader" data-trigger="getRankSection">
                                <span class="loader__spinner" wire:ignore></span>
                            </div>
                            <div class="card-body">
                                <div class="card-body__header">
                                    <div class="card-body__title">Audit Mutu Internal</div>
                                    <div class="card-body__desc">{{ $this->nowPeriod->period_text ?? '-' }}</div>
                                </div>
                                <div class="card-body__main section-default" style="max-height: none">
                                    <div class="grid">
                                        <div class="col-12 col-sm-5">
                                            <div class="percentage-box">
                                                <div class="percentage-box__text">
                                                    @php
                                                        $rankIncrease =
                                                            $rankSection['information']['rank_increase'] ?? 0;
                                                        $growthClass = $rankIncrease >= 0 ? 'up' : 'down';
                                                    @endphp
                                                    <span>{{ $rankSection['information']['average_score'] ?? 0 }}</span>
                                                    <span class="growth-{{ $growthClass }}">
                                                        <span class="icon icon-arrow-{{ $growthClass }}"></span>
                                                        <span
                                                            class="growth-{{ $growthClass }}__text">{{ $rankSection['information']['rank_increase'] ?? 0 }}%</span>
                                                    </span>
                                                </div>
                                                <div class="percentage-box__desc">
                                                    @php
                                                        $rank = $rankSection['information']['rank'] ?? null;
                                                        $rankName = $rank?->nama_spmi_peringkat;
                                                        $rankNum =
                                                            $rankSection['information']['rank_detail']['summary'] ??
                                                            null;
                                                    @endphp
                                                    <span>
                                                        @if (!empty($rankName) && $rankSection['information']['average_score'] !== '0.00')
                                                            Hasil predikat Anda
                                                            <span class="rank">
                                                                <span class="rank-num">
                                                                    {{ $rankNum }}
                                                                </span>
                                                                <b>{{ $rankName }}</b>
                                                            </span>
                                                        @else
                                                            Anda belum memiliki predikat
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-7" style="position: relative">
                                            <div id="empty-final-score"
                                                class="util_flex-center util_d-none util_flex-column util_flex-middle"
                                                style="position: absolute; top:50%; left: 50%; transform: translate(-50%, -50%);
                                                    background: #ffffff; border-radius: 8px; border: 1px solid #c6c6c6; padding: 12px">
                                                <h3 style="margin-bottom: 8px; text-align: center; font-size: 14px;">
                                                    Belum ada data
                                                </h3>
                                                <p
                                                    style="color: #727272; margin-bottom: 12px; text-align: center; font-size: 16px;">
                                                    Statistik Audit Mutu Internal perguruan tinggi nanti akan
                                                    ditampilkan disini
                                                </p>
                                            </div>
                                            <canvas wire:ignore.self id="final-score-chart"></canvas>
                                        </div>
                                    </div>
                                    <div class="item-percentage-box">
                                        {{-- Persentase Pengisian Laporan Kinerja --}}
                                        <div class="item-percentage-box__items">
                                            <div class="title">
                                                <div class="icon icon-document-solid"></div>
                                                <div class="text">Pengisian Laporan Kinerja</div>
                                            </div>
                                            <div class="body">
                                                <div class="percentage">
                                                    <span>{{ $rankSection['progress']['filling_indicator'] ?? 0 }}</span>
                                                    <span class="percent">%</span>
                                                </div>
                                            </div>
                                            <x-core::button class="btn-detail" target="_blank" type="button"
                                                variant="link"
                                                href="{{ route('spmi.pengisian-indikator.index', ['filter' => ['id_audit_periode' => $nowPeriod?->id]]) }}">
                                                Lihat Detail
                                            </x-core::button>
                                        </div>

                                        {{-- Persentase Pengisian Evaluasi Diri --}}
                                        <div class="item-percentage-box__items">
                                            <div class="title">
                                                <div class="icon icon-star-solid"></div>
                                                <div class="text">Pengisian Evaluasi Diri</div>
                                            </div>
                                            <div class="body">
                                                <div class="percentage">
                                                    <span>{{ $rankSection['progress']['filling_self_indicator'] ?? 0 }}</span>
                                                    <span class="percent">%</span>
                                                </div>
                                            </div>
                                            <x-core::button class="btn-detail" target="_blank" type="button"
                                                variant="link"
                                                href="{{ route('spmi.pengisian-indikator-led.index', ['filter' => ['id_audit_periode' => $nowPeriod?->id]]) }}">
                                                Lihat Detail
                                            </x-core::button>
                                        </div>

                                        {{-- Persentase Target Capaian --}}
                                        <div class="item-percentage-box__items">
                                            <div class="title">
                                                <div class="icon icon-stop-circle-solid"></div>
                                                <div class="text">Atur Target Capaian</div>
                                            </div>
                                            <div class="body">
                                                <div class="percentage">
                                                    <span>{{ $rankSection['progress']['nilai_target'] ?? 0 }}</span>
                                                    <span class="percent">%</span>
                                                </div>
                                            </div>
                                            <x-core::button class="btn-detail" target="_blank" target="_blank"
                                                type="button" variant="link"
                                                href="{{ route('spmi.target-indikator.index', ['filter' => ['id_audit_periode' => $nowPeriod?->id]]) }}">
                                                Lihat Detail
                                            </x-core::button>
                                        </div>

                                        {{-- Persentase Penilaian Oleh Auditor --}}
                                        <div class="item-percentage-box__items">
                                            <div class="title">
                                                <div class="icon icon-users-solid"></div>
                                                <div class="text">Penilaian Oleh Auditor</div>
                                            </div>
                                            <div class="body">
                                                <div class="percentage">
                                                    <span>{{ $rankSection['progress']['auditor_assessment'] ?? 0 }}</span>
                                                    <span class="percent">%</span>
                                                </div>
                                            </div>
                                            <x-core::button class="btn-detail" target="_blank" type="button"
                                                variant="link"
                                                href="{{ route('spmi.penilaian-auditor.index', ['filter' => ['id_audit_periode' => $nowPeriod?->id]]) }}">
                                                Lihat Detail
                                            </x-core::button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" wire:ignore.self>
                        <div class="card">
                            <div class="loader" data-trigger="getCriteriaChartSection">
                                <span class="loader__spinner" wire:ignore></span>
                            </div>
                            <div class="card-body">
                                <div class="card-body__header">
                                    <div class="card-body__title">Ketercapaian AMI</div>
                                    <div class="card-body__desc">{{ $this->nowPeriod->period_text ?? '-' }}</div>
                                </div>
                                <div class="card-body__main section-default"
                                    style="max-height: none; padding-top: 0px !important;">
                                    <div class="criteria-chart-box">
                                        <canvas wire:ignore.self id="criteria-chart"></canvas>
                                    </div>
                                    <div class="util_d-flex util_flex-between util_flex-center-vertical">
                                        <div class="util_d-flex gap-16">
                                            <div
                                                class="util_d-flex util_flex-center util_flex-middle util_flex-center gap-8">
                                                <span class="dot dot-yellow util_h-fit-content"
                                                    style="padding: 8px"></span>
                                                <p style="font-size: 12.6px">Target AMI</p>
                                            </div>
                                            <div
                                                class="util_d-flex util_flex-center util_flex-middle util_flex-center gap-8">
                                                <span class="dot dot-light-blue util_h-fit-content"
                                                    style="padding: 8px"></span>
                                                <p style="font-size: 12.6px">Realisasi AMI</p>
                                            </div>
                                        </div>
                                        <div>
                                            <a @if (!empty($idHasilAkhir)) href="{{ route('spmi.hasil-akhir-audit.show', $idHasilAkhir) }}" @else href="{{ route('spmi.hasil-akhir-audit.index') }}" @endif
                                                rel="noopener" class="btn btn_link btn-detail" target="_blank">
                                                <u>Lihat Detail</u>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="grid">
                    <div class="col-12 col-md-4" wire:ignore.self>
                        <div class="card">
                            <div class="loader" data-trigger="getScheduleSection">
                                <span class="loader__spinner" wire:ignore></span>
                            </div>
                            <div class="card-body p-24">
                                <div class="card-body__header">
                                    <div class="card-body__title">Jadwal Pelaksanaan AMI</div>
                                    <div class="card-body__desc">{{ $this->nowPeriod->period_text ?? '-' }}</div>
                                </div>
                                <div class="card-body__main">
                                    <div class="section-list">
                                        <div class="schedule-tabs">
                                            <div class="schedule-tabs__item {{ !$scheduleState['isScheduled'] ? 'active' : '' }}"
                                                wire:click="getScheduleSection(false)"
                                                onclick="showLoader('getScheduleSection')">Belum Terjadwal</div>
                                            <div class="schedule-tabs__item {{ $scheduleState['isScheduled'] ? 'active' : '' }}"
                                                wire:click="getScheduleSection(true)"
                                                onclick="showLoader('getScheduleSection')">Sudah Terjadwal</div>
                                        </div>
                                        @if (!$scheduleState['isScheduled'])
                                            <x-core::alert class="alert-schedule" variant="warning" :dismissable="false">
                                                Segera jadwalkan untuk dapat melakukan proses AMI
                                            </x-core::alert>
                                        @endif
                                        <div class="card-list">
                                            @php
                                                $scheduleSort = $scheduleState['sort'] == 'asc' ? 'desc' : 'asc';
                                            @endphp
                                            <div class="card-list__header">
                                                @if (!empty($scheduleSection))
                                                    <div class="title">Daftar Unit Kerja</div>
                                                    <div class="button"
                                                        wire:click="getScheduleSection({{ $scheduleState['isScheduled'] ? 'true' : 'false' }}, '{{ $scheduleSort }}')"
                                                        onclick="showLoader('getScheduleSection')">
                                                        <span class="icon icon-arrows-up-down"></span> Urutkan
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="card-list__body">
                                                @foreach ($scheduleSection as $schedule)
                                                    <div class="list-item">
                                                        <div
                                                            class="list-item__section {{ $scheduleState['isScheduled'] ? 'scheduled' : '' }}">
                                                            <div class="section-icon">
                                                                <span class="icon icon-calendar-days-solid"></span>
                                                            </div>
                                                            <div class="section-info">
                                                                <h3>{{ $schedule['nama_unit'] }}</h3>
                                                                <p style="font-size: 16px;">
                                                                    {{ $schedule['assessment_date'] ?? 'Belum ada jadwal' }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="list-item__section">
                                                            <x-core::button class="btn-detail" target="_blank"
                                                                type="button" variant="link"
                                                                href="{{ $scheduleState['isScheduled'] ? route('spmi.jadwal-audit.show', [$schedule['id']]) : route('spmi.jadwal-audit.create') }}">
                                                                <u>{{ $scheduleState['isScheduled'] ? 'Lihat' : 'Buat' }}</u>
                                                                <span
                                                                    class="icon icon-arrow-top-right-on-square"></span>
                                                            </x-core::button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @empty($scheduleSection)
                                                    <div
                                                        class="util_d-flex util_flex-center util_flex-column util_flex-middle">
                                                        <img src="{{ asset('images/empty/calendar.svg') }}"
                                                            width="200px" />
                                                        <h3 style="margin-bottom: 8px">Daftar Unit Kerja</h3>
                                                        <p style="font-size: 16px; color: #727272; margin-bottom: 12px">
                                                            Belum terdapat
                                                            unit kerja yang dijadwalkan
                                                        </p>
                                                    </div>
                                                @endempty
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" wire:ignore.self>
                        <div class="card">
                            <div class="loader" data-trigger="getAuditorPersonSection">
                                <span class="loader__spinner" wire:ignore></span>
                            </div>
                            <div class="card-body p-24">
                                <div class="card-body__header">
                                    <div class="card-body__title">Penugasan Auditor</div>
                                    <div class="card-body__desc">{{ $this->nowPeriod->period_text ?? '-' }}</div>
                                </div>
                                <div class="card-body__main">
                                    <div class="section-list">
                                        <div class="scale-detail">
                                            <div class="scale-detail__item">
                                                <h3 style="font-size: 36px;">
                                                    {{ $auditorPersonSection['total_auditor'] ?? 0 }}
                                                    @php
                                                        $totalAuditorIncrease =
                                                            $auditorPersonSection['total_auditor_increase'] ?? 0;
                                                        $growthClass = $totalAuditorIncrease > 0 ? 'up' : 'down';
                                                    @endphp
                                                    <span class="growth-{{ $growthClass }}">
                                                        <span class="icon icon-arrow-{{ $growthClass }}"></span>
                                                        <span
                                                            class="growth-{{ $growthClass }}__text">{{ $totalAuditorIncrease ?? 0 }}%</span>
                                                    </span>
                                                </h3>
                                                <p>Jumlah Auditor</p>
                                            </div>

                                            <div class="scale-detail__item">
                                                <h3 style="font-size: 36px;">
                                                    {{ count($auditorPersonSection['data'] ?? []) > 0 ? 1 : 0 }}:{{ $auditorPersonSection['average_study_program'] ?? 0 }}
                                                    @php
                                                        $averageStudyProgramIncrease =
                                                            $auditorPersonSection['total_study_program_increase'] ?? 0;
                                                        $growthClass = $averageStudyProgramIncrease > 0 ? 'up' : 'down';
                                                    @endphp
                                                    <span class="growth-{{ $growthClass }} reverse">
                                                        <span class="icon icon-arrow-{{ $growthClass }}"></span>
                                                        <span
                                                            class="growth-{{ $growthClass }}__text">{{ $averageStudyProgramIncrease ?? 0 }}%</span>
                                                    </span>
                                                </h3>
                                                <p>Auditor : Unit Kerja</p>
                                            </div>
                                        </div>
                                        <div class="card-list">
                                            <div class="card-list__header">
                                                @php
                                                    $auditorPersonSort =
                                                        $auditorPersonState['sort'] == 'asc' ? 'desc' : 'asc';
                                                @endphp
                                                @if (!empty($auditorPersonSection['data']))
                                                    <div class="title">Daftar Nama Auditor</div>
                                                    <div class="button"
                                                        wire:click="getAuditorPersonSection('{{ $auditorPersonSort }}')"
                                                        onclick="showLoader('getAuditorPersonSection')">
                                                        <span class="icon icon-arrows-up-down"></span> Urutkan
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="card-list__body">
                                                @foreach ($auditorPersonSection['data'] ?? [] as $person)
                                                    @php
                                                        $totalStudyProgram = $person['total_study_program'];

                                                        if ($totalStudyProgram >= 0 && $totalStudyProgram <= 2) {
                                                            $badgeAccent = 'primary';
                                                        } elseif ($totalStudyProgram >= 3 && $totalStudyProgram <= 4) {
                                                            $badgeAccent = 'warning';
                                                        } else {
                                                            $badgeAccent = 'danger';
                                                        }
                                                    @endphp
                                                    <div class="list-item">
                                                        <div class="list-item__section">
                                                            <div class="section-icon">
                                                                <div class="avatar">
                                                                    <p class="avatar__acronym">
                                                                        {{ substr($person['original_name'], 0, 2) }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="section-info">
                                                                <h3>{{ $person['name'] }}</h3>
                                                                <p class="default">
                                                                    {{ substr($person['nama_unit'], 0, 3) == ' - ' ? str_replace('-', ' ', $person['nama_unit']) : $person['nama_unit'] }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="list-item__section util_flex-middle">
                                                            <span class="badge badge_secondary-{{ $badgeAccent }}">
                                                                {{ $person['total_study_program'] }} Unit Kerja
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @empty($auditorPersonSection['data'])
                                                    <div
                                                        class="util_d-flex util_flex-center util_flex-column util_flex-middle">
                                                        <img src="{{ asset('images/empty/document.svg') }}"
                                                            width="200px" />
                                                        <h3 style="margin-bottom: 8px">Daftar Nama Auditor</h3>
                                                        <p style="font-size: 16px; color: #727272; margin-bottom: 12px">
                                                            Belum terdapat
                                                            auditor yang dialokasikan
                                                        </p>
                                                    </div>
                                                @endempty
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4" wire:ignore.self>
                        <div class="card">
                            <div class="loader" data-trigger="getPenilaianAuditorSection">
                                <span class="loader__spinner" wire:ignore></span>
                            </div>
                            <div class="card-body p-24">
                                <div class="card-body__header">
                                    <div class="card-body__title">Penilaian Auditor</div>
                                    <div class="card-body__desc">{{ $this->nowPeriod->period_text ?? '-' }}</div>
                                </div>
                                <div class="card-body__main">
                                    <div class="section-list">
                                        <div class="progress-bar">
                                            @php
                                                $progressAssessmentPercent =
                                                    $rankSection['progress']['auditor_assessment2'] ?? 0;
                                            @endphp
                                            <div class="progress-bar__header">
                                                <div class="header-title">Progres Penilaian</div>
                                                <div class="header-desc">{{ $progressAssessmentPercent }}% selesai
                                                </div>
                                            </div>
                                            <div class="progress-bar__bar">
                                                <div class="bar-background"></div>
                                                <div class="bar-fill" @style([
                                                    "width: {$progressAssessmentPercent}%" => $progressAssessmentPercent,
                                                ])></div>
                                            </div>
                                        </div>
                                        <div class="card-list">
                                            <div class="card-list__header">
                                                @php
                                                    $PenilaianAuditorSort =
                                                        $PenilaianAuditorState['sort'] == 'asc' ? 'desc' : 'asc';
                                                @endphp
                                                @if (count($PenilaianAuditorSection) > 0)
                                                    <div class="title">Daftar Unit Kerja</div>
                                                    <div class="button"
                                                        wire:click="getPenilaianAuditorSection('{{ $PenilaianAuditorSort }}')"
                                                        onclick="showLoader('getPenilaianAuditorSection')">
                                                        <span class="icon icon-arrows-up-down"></span> Urutkan
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="card-list__body">
                                                @foreach ($PenilaianAuditorSection ?? [] as $assessment)
                                                    @php
                                                        $totalFilledPercent = (int) round(
                                                            $assessment['total_penilaian_terisi_percent'],
                                                            0,
                                                            PHP_ROUND_HALF_UP,
                                                        );
                                                        $totalNotFilledMatrix =
                                                            $assessment['total_indikator_matriks'] -
                                                            $assessment['total_penilaian_terisi'];

                                                        if ($totalFilledPercent >= 30 && $totalFilledPercent <= 50) {
                                                            $badgeAccent = 'warning';
                                                        } elseif (
                                                            $totalFilledPercent >= 0 &&
                                                            $totalFilledPercent <= 29
                                                        ) {
                                                            $badgeAccent = 'danger';
                                                        } else {
                                                            $badgeAccent = 'primary';
                                                        }
                                                    @endphp
                                                    <div class="list-item">
                                                        <div class="list-item__section">
                                                            <div class="section-info">
                                                                <h3>{{ $assessment['nama_unit'] }}</h3>
                                                                <p class="default">
                                                                    @if ($totalNotFilledMatrix === 0)
                                                                        Semua indikator sudah dinilai
                                                                    @else
                                                                        {{ $totalNotFilledMatrix }}/
                                                                        {{ $assessment['total_indikator_matriks'] }}
                                                                        indikator
                                                                        belum dinilai
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="list-item__section util_flex-middle">
                                                            <span class="badge badge_secondary-{{ $badgeAccent }}">
                                                                {{ $totalFilledPercent }}%
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @empty($PenilaianAuditorSection)
                                                    <div
                                                        class="util_d-flex util_flex-center util_flex-column util_flex-middle">
                                                        <img src="{{ asset('images/empty/default.svg') }}"
                                                            width="200px" />
                                                        <h3 style="margin-bottom: 8px;">Daftar Penilaian Auditor</h3>
                                                        <p style="font-size: 16px; color: #727272; margin-bottom: 12px">
                                                            Belum terdapat
                                                            data penilaian auditor
                                                        </p>
                                                    </div>
                                                @endempty
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="grid">
                    <div class="col-12" wire:ignore.self>
                        <div class="card">
                            <div class="col-12" wire:ignore.self>
                                <div class="card-finding">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-body__header">
                                                <div class="card-body__title">Temuan Audit Mutu Internal</div>
                                                <div class="card-body__desc">
                                                    {{ $this->nowPeriod->period_text ?? '-' }}</div>
                                            </div>
                                            <div class="overflow">
                                                <div class="audit-finding-detail">
                                                    <div class="audit-finding-detail__item">
                                                        <h3>
                                                            @php
                                                                $averageKtsMayorIncrease =
                                                                    $auditFindingSection['total_kts_mayor_increase'];
                                                                $growthClass =
                                                                    $averageKtsMayorIncrease > 0 ? 'up' : 'down';
                                                            @endphp
                                                            {{ $auditFindingSection['average']['kts_mayor'] ?? 0 }}
                                                            <span class="growth-{{ $growthClass }} reverse">
                                                                <span
                                                                    class="icon icon-arrow-{{ $growthClass }}"></span>
                                                                <span
                                                                    class="growth-{{ $growthClass }}__text">{{ $averageKtsMayorIncrease }}%</span>
                                                            </span>
                                                        </h3>
                                                        <div
                                                            class="util_d-flex util_flex-middle util_flex-middle gap-12">
                                                            {{-- <span class="dot dot-ktmy util_h-fit-content"></span> --}}
                                                            <p>KTS Mayor</p>
                                                        </div>
                                                    </div>

                                                    <div class="audit-finding-detail__item">
                                                        <h3>
                                                            @php
                                                                $averageKtsMinorIncrease =
                                                                    $auditFindingSection['total_kts_minor_increase'];
                                                                $growthClass =
                                                                    $averageKtsMinorIncrease > 0 ? 'up' : 'down';
                                                            @endphp
                                                            {{ $auditFindingSection['average']['kts_minor'] ?? 0 }}
                                                            <span class="growth-{{ $growthClass }} reverse">
                                                                <span
                                                                    class="icon icon-arrow-{{ $growthClass }}"></span>
                                                                <span
                                                                    class="growth-{{ $growthClass }}__text">{{ $averageKtsMinorIncrease }}%</span>
                                                            </span>
                                                        </h3>
                                                        <div
                                                            class="util_d-flex util_flex-middle util_flex-middle gap-12">
                                                            {{-- <span class="dot dot-ktmn util_h-fit-content"></span> --}}
                                                            <p>KTS Minor</p>
                                                        </div>
                                                    </div>

                                                    <div class="audit-finding-detail__item">
                                                        <h3>
                                                            @php
                                                                $averageObservationIncrease =
                                                                    $auditFindingSection['total_observation_increase'];
                                                                $growthClass =
                                                                    $averageObservationIncrease > 0 ? 'up' : 'down';
                                                            @endphp
                                                            {{ $auditFindingSection['average']['observation'] ?? 0 }}
                                                            <span class="growth-{{ $growthClass }} reverse">
                                                                <span
                                                                    class="icon icon-arrow-{{ $growthClass }}"></span>
                                                                <span
                                                                    class="growth-{{ $growthClass }}__text">{{ $averageObservationIncrease }}%</span>
                                                            </span>
                                                        </h3>
                                                        <div
                                                            class="util_d-flex util_flex-middle util_flex-middle gap-12">
                                                            {{-- <span class="dot dot-obs util_h-fit-content"></span> --}}
                                                            <p>Observasi</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body__main section-default"
                                                style="max-height: fit-content; position: relative">
                                                <div class="loader" data-trigger="getAuditTemuanDataSection"
                                                    wire:loading.flex
                                                    wire:target="gotoPageAuditTemuan, setPerPageAuditTemuan, previousPageAuditTemuan, nextPageAuditTemuan">
                                                    <span class="loader__spinner" wire:ignore></span>
                                                </div>
                                                <div class="audit-finding-chart-box" style="position:relative">
                                                    @if (empty($auditTemuanData?->items))
                                                        <div
                                                            class="util_d-flex util_flex-center util_flex-column util_flex-middle">
                                                            <img src="{{ asset('images/empty/default.svg') }}"
                                                                width="200px" />
                                                            <h3 style="margin-bottom: 8px">Belum ada data
                                                            </h3>
                                                            <p
                                                                style="font-size: 16px; color: #727272; margin-bottom: 12px">
                                                                Statistik Temuan Audit Mutu Internal akan ditampilkan
                                                                disini
                                                                saat sudah tersedia
                                                            </p>
                                                        </div>
                                                    @else
                                                        <div class="box-table">
                                                            <div class="box-table__content"
                                                                style="padding: 0; border: none;">
                                                                <table>
                                                                    <thead>
                                                                        <tr>
                                                                            <th rowspan="2" style="width: 10px">No.
                                                                            </th>
                                                                            <th rowspan="2">Nama Unit Kerja</th>
                                                                            <th rowspan="2" style="width: 10%">KTS
                                                                                Mayor</th>
                                                                            <th rowspan="2" style="width: 10%">KTS
                                                                                Minor</th>
                                                                            <th rowspan="2" style="width: 10%">
                                                                                Observasi</th>
                                                                            <th rowspan="2" style="width: 10px">
                                                                                Aksi</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($auditTemuanData?->items ?? [] as $item)
                                                                            <tr>
                                                                                <td>{{ $loop->iteration + ($recapFinalScoreState['page'] > 1 ? $recapFinalScoreState['page'] * 10 : 0) }}
                                                                                </td>
                                                                                <td>
                                                                                    {{ $item['nama_unit'] }}
                                                                                </td>
                                                                                <td style="text-align: right">
                                                                                    {{ $item['total_kts_mayor'] ?? 'NA' }}
                                                                                </td>
                                                                                <td style="text-align: right">
                                                                                    {{ $item['total_kts_minor'] ?? 'NA' }}
                                                                                </td>
                                                                                <td style="text-align: right">
                                                                                    {{ $item['total_observation'] ?? 'NA' }}
                                                                                </td>
                                                                                <td>
                                                                                    <x-core::button class="btn-detail"
                                                                                        target="_blank" type="button"
                                                                                        variant="outline"
                                                                                        leadingIcon="eye-solid"
                                                                                        size="xs"
                                                                                        href="{{ route('spmi.audit-temuan.show', [$item['id']]) }}">
                                                                                    </x-core::button>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            @if (!empty($auditTemuanData))
                                                                <div class="box-table__footer" style="padding: 0">
                                                                    <x-core::table.navigation :data="$auditTemuanData"
                                                                        nextPage="nextPageAuditTemuan"
                                                                        previousPage="previousPageAuditTemuan"
                                                                        setPerPage="setPerPageAuditTemuan"
                                                                        gotoPage="gotoPageAuditTemuan" />
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        //temuan
                                        $items = $auditFindingSection['most_findings'] ?? [];
                                    @endphp
                                    <div class="finding-section">
                                        <div class="loader" data-trigger="getAuditTemuanSection">
                                            <span class="loader__spinner" wire:ignore></span>
                                        </div>
                                        <div class="finding-section__list">
                                            <table class="finding-table">
                                                <thead>
                                                    <tr>
                                                        @php
                                                            $totalTemuan = '';
                                                            if (!empty($auditFindingSection['most_findings'])) {
                                                                $totalTemuan = count(
                                                                    $auditFindingSection['most_findings'],
                                                                );
                                                                $totalTemuan = $totalTemuan < 10 ? '' : $totalTemuan;
                                                            }
                                                        @endphp
                                                        <th>{{ $totalTemuan }}
                                                            Daftar Temuan Audit Mutu Internal Teratas</th>
                                                        <th style="width: 15%">Kategori</th>
                                                        <th style="width: 10%">Jumlah Unit Kerja</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($auditFindingSection['most_findings'] ?? [] as $mostFinding)
                                                        <tr>
                                                            <td>
                                                                <div class="util_d-flex util_flex-start util_flex-middle gap-12"
                                                                    style="padding:12px 0px">
                                                                    <span class="number">{{ $loop->index + 1 }}</span>
                                                                    <div style="width: 70%">
                                                                        {!! $mostFinding['pertanyaan_penilaian'] !!}
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $dotClass = null;

                                                                    if ($mostFinding['jenis_temuan'] == 1) {
                                                                        $dotClass = 'obs';
                                                                    } elseif ($mostFinding['jenis_temuan'] == 2) {
                                                                        $dotClass = 'ktmn';
                                                                    } else {
                                                                        $dotClass = 'ktmy';
                                                                    }
                                                                @endphp
                                                                <div
                                                                    class="util_d-flex util_flex-center util_flex-middle util_flex-start gap-12">
                                                                    <span
                                                                        class="dot dot-{{ $dotClass }} util_h-fit-content"></span>
                                                                    <p>{{ $mostFinding['finding_type_name'] }}</p>
                                                                </div>
                                                            </td>
                                                            <td style="text-align: right">
                                                                {{ $mostFinding['total_finding'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @if (!empty($auditFindingSection['most_findings']))
                                                <x-core::button class="btn-expand" type="button" variant="link"
                                                    href="{{ route('spmi.hasil-audit-temuan.index') }}?filter[id_audit_periode]={{ $filterPeriod }}&filter[id_unit_kerja]={{ $filterStudyProgram ?? '-' }}">
                                                    <u>Lihat Detail</u>
                                                </x-core::button>
                                            @endif
                                            @empty($auditFindingSection['most_findings'])
                                                <div
                                                    class="util_d-flex util_flex-center util_flex-column util_flex-middle">
                                                    <img src="{{ asset('images/empty/default.svg') }}" width="200px" />
                                                    <h3 style="margin-bottom: 8px">Daftar Temuan Audit Mutu Internal
                                                        Teratas</h3>
                                                    <p style="font-size: 16px; color: #727272; margin-bottom: 12px">Belum
                                                        terdapat daftar
                                                        temuan yang ditemukan
                                                    </p>
                                                </div>
                                            @endempty
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="grid">
                    <div class="col-12" wire:ignore.self>
                        <div class="card">
                            <div class="loader" data-trigger="getRecapFinalScoreSection" wire:loading.flex
                                wire:target="gotoPageFinalRecap, setPerPageFinalRecap, previousPageFinalRecap, nextPageFinalRecap">
                                <span class="loader__spinner" wire:ignore></span>
                            </div>
                            <div class="card-body">
                                <div class="card-body__header">
                                    <div class="card-body__title">Rekapitulasi Skor Akhir</div>
                                    <div class="card-body__desc">{{ $this->nowPeriod->period_text ?? '-' }}</div>
                                </div>
                                <div class="card-body__main section-default" style="max-height: none; padding-top: 0">
                                    @if (empty($recapFinalScoreSection?->items))
                                        <div class="util_d-flex util_flex-center util_flex-column util_flex-middle">
                                            <img src="{{ asset('images/empty/default.svg') }}" width="200px" />
                                            <h3 style="margin-bottom: 8px">Rekapitulasi Skor Akhir
                                            </h3>
                                            <p style="font-size: 16px; color: #727272; margin-bottom: 12px">Belum
                                                terdapat data
                                                rekapitulasi skor akhir
                                            </p>
                                        </div>
                                    @else
                                        <div class="box-table">
                                            <div class="table-max table-max_absolute table-max_with-left-shadow">
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th>No.</th>
                                                            <th>Unit Kerja</th>
                                                            <th>Nama Penilaian Panduan</th>
                                                            <th>Skor Akhir SPME </th>
                                                            <th>Skor Akhir AMI</th>
                                                            <th style="width: 10px">Peringkat Capaian AMI</th>
                                                            <th style="width: 10px">Status Akreditasi Eksternal</th>
                                                            <th style="width: 10px">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($recapFinalScoreSection?->items ?? [] as $item)
                                                            <tr>
                                                                <td>{{ $loop->iteration + ($recapFinalScoreState['page'] > 1 ? $recapFinalScoreState['page'] * 10 : 0) }}
                                                                </td>
                                                                <td>
                                                                    <div class="util_d-flex util_flex-middle"
                                                                        style="gap: 8px">
                                                                        <p>{{ $item['nama_unit'] }}</p>
                                                                        @if ($item['apakah_terfinalisasi'] === false)
                                                                            <span class="badge badge_secondary-warning"
                                                                                style="font-size: 12px">
                                                                                Proses Penilaian
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td>{{ $item['nama_penilaian_panduan'] }}</td>
                                                                <td style="text-align: right">
                                                                    {{ $item['nilai_iku'] ?? 'NA' }}</td>
                                                                <td style="text-align: right">
                                                                    {{ $item['persentase_nilai_akhir'] ?? 'NA' }}</td>
                                                                <td>{{ $item['nama_spmi_peringkat'] }}
                                                                </td>
                                                                <td>{{ $item['nama_peringkat_akreditasi'] }}</td>
                                                                <td>
                                                                    <x-core::button class="btn-detail" target="_blank"
                                                                        type="button" variant="outline"
                                                                        leadingIcon="eye-solid" size="xs"
                                                                        href="{{ !empty($item['id']) ? route('spmi.hasil-akhir-audit.show', [$item['id']]) : '#' }}"
                                                                        disabled="{{ empty($item['id']) || !$item['apakah_terfinalisasi'] }}">
                                                                    </x-core::button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @if (!empty($recapFinalScoreSection))
                                                <div class="box-table__footer" style="padding: 0">
                                                    <x-core::table.navigation :data="$recapFinalScoreSection"
                                                        gotoPage="gotoPageFinalRecap" nextPage="nextPageFinalRecap"
                                                        previousPage="previousPageFinalRecap"
                                                        setPerPage="setPerPageFinalRecap" />
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <br>
                            </div>
                        </div>
                    </div>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            dispatcher();

            Livewire.on('finishGetRankSection', ([chartData]) => {
                const category = chartData?.chart?.nilai_akhir?.category ?? [];
                const chartDataSkorAkhir = chartData?.chart?.nilai_akhir?.data?.nilai_akhir ?? [];
                const chartDataSkorTarget = chartData?.chart?.nilai_akhir?.data?.nilai_target ?? [];

                const emptyChart = chartDataSkorAkhir.every((item) => parseInt(item) === 0);

                buildFinalScoreChart(category, chartDataSkorAkhir, chartDataSkorTarget);

                setTimeout(() => {
                    if (emptyChart) {
                        document.querySelector("#empty-final-score").classList.remove(
                            'util_d-none');
                    }
                }, 100);
            });

            Livewire.on('finishGetCriteriaChartSection', ([chartData]) => {
                const category = chartData?.category ?? [];
                const chartDataKetercapaian = chartData?.data?.ketercapaian ?? [];
                const chartDataBobotTarget = chartData?.data?.bobot_target ?? [];
                buildCriteriaChart(category, chartDataKetercapaian, chartDataBobotTarget);
            });

            // NOTE: Sementara tidak dipakai
            // Livewire.on('finishGetAuditTemuanSection', ([chartData]) => {
            //     const category = chartData?.chart?.category ?? [];
            //     const observation = chartData?.chart?.data?.observation ?? [];
            //     const ktsMayor = chartData?.chart?.data?.kts_mayor ?? [];
            //     const ktsMinor = chartData?.chart?.data?.kts_minor ?? [];

            //     buildAuditTemuanChart(category, observation, ktsMayor, ktsMinor);

            //     const isEmptyCategory = category.filter((item) => item !== '').length === 0;
            //     if (isEmptyCategory) {
            //         setTimeout(() => {
            //             document.querySelector("#empty-temuan").classList.remove('util_d-none');
            //         }, 100);
            //     }
            // });

            Livewire.hook('element.init', ({
                el
            }) => {
                if (el.id == 'filter-study-program' || el.id == 'filter-period') {
                    el.addEventListener('change', () => {
                        dispatcher(el.id);
                    });
                }
            });
        });

        function dispatcher(elementId = null) {
            livewireDispatch('getRankSection');
            livewireDispatch('getCriteriaChartSection');
            livewireDispatch('getPenilaianAuditorSection');
            livewireDispatch('getAuditTemuanSection');
            livewireDispatch('getRecapFinalScoreSection');
            livewireDispatch('getAuditTemuanDataSection');

            if (elementId !== null && elementId == 'filter-period') {
                livewireDispatch('getScheduleSection');
                livewireDispatch('getAuditorPersonSection');
            }

            if (elementId === null) {
                livewireDispatch('getScheduleSection');
                livewireDispatch('getAuditorPersonSection');
            }
        }

        function livewireDispatch(event) {
            showLoader(event);
            Livewire.dispatch(event);
        }

        function showLoader(event, status = true) {
            const loaders = document.querySelectorAll(`.loader[data-trigger="${event}"]`);
            loaders.forEach((loader) => {
                if (status) {
                    loader.classList.add('active');
                } else {
                    loader.classList.remove('active');
                }
            });
        }

        function buildFinalScoreChart(category = [], chartDataSkorAkhir = [], chartDataSkorTarget = []) {
            const getGradient = (stop1, stop2) => (
                (context) => {
                    const {
                        ctx,
                        chartArea
                    } = context.chart;

                    if (!chartArea) return;

                    const chartWidth = chartArea.right - chartArea.left;
                    const chartHeight = chartArea.bottom - chartArea.top;
                    const width = chartWidth;
                    const height = chartHeight;
                    const gradient = ctx.createLinearGradient(15, chartArea.bottom, 0, chartArea.top);
                    gradient.addColorStop(0.5, stop1);
                    gradient.addColorStop(0, stop2);

                    return gradient;
                }
            )

            const data = {
                labels: category,
                datasets: [{
                    label: 'Realisasi AMI',
                    data: chartDataSkorAkhir,
                    fill: false,
                    borderColor: '#3288ff',
                    tension: 0.1,
                    pointRadius: 5,
                    pointBackgroundColor: '#3288ff',
                    pointBorderColor: '#FFF',
                }, {
                    label: 'Target AMI',
                    data: chartDataSkorTarget,
                    fill: false,
                    borderColor: '#ff8832',
                    tension: 0.1,
                    borderDash: [5, 3],
                    pointRadius: 5,
                    pointBackgroundColor: '#ff8832',
                    pointBorderColor: '#FFF',
                }]
            };

            const config = {
                type: 'line',
                data: data,
                options: {
                    plugins: {
                        legend: {
                            align: 'end',
                            display: true,
                            labels: {
                                useBorderRadius: true,
                                borderRadius: 5,
                                padding: 15,
                            },
                        }
                    },
                    scales: {
                        y: {
                            display: true,
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 20,
                            },
                            border: {
                                dash: [5, 3],
                                display: false,
                            },

                        },
                        x: {
                            grid: {
                                display: false,
                            },
                        }
                    },
                }
            };

            buildChart('final-score-chart', config);
        }

        function buildAuditTemuanChart(category = [], observation = [], ktsMayor = [], ktsMinor = []) {
            const separator = {
                label: 'separator',
                data: category.map(() => 0.2),
                backgroundColor: ['transparent'],
            }

            const data = {
                labels: category,
                datasets: [{
                        label: 'KTS Mayor',
                        data: ktsMayor,
                        backgroundColor: '#d0524a',
                        borderWidth: 2,
                        borderColor: "transparent"
                    },
                    {
                        label: 'KTS Minor',
                        data: ktsMinor,
                        backgroundColor: '#db913d',
                        borderWidth: 2,
                        borderColor: "transparent"
                    },
                    {
                        label: 'Observasi',
                        data: observation,
                        backgroundColor: '#f2d051',
                        borderWidth: 2,
                        borderColor: "transparent"
                    },
                ],
            };

            const config = {
                type: 'bar',
                data: data,
                options: {
                    maintainAspectRatio: false,
                    interaction: {
                        mode: "index",
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: false,
                        },
                        tooltip: {
                            filter: function(tooltipItem, data) {
                                return tooltipItem.label.length !== 0;
                            },
                            callbacks: {
                                label: function(context) {
                                    // Hide tooltip for separator
                                    if (context.dataset.label.includes("separator")) {
                                        return null;
                                    }
                                    return `${context.dataset.label}: ${context.formattedValue}`;
                                }
                            }
                        },
                    },
                    borderSkipped: false,
                    maintainAspectRatio: false,
                    elements: {
                        bar: {
                            borderRadius: {
                                topLeft: 8,
                                topRight: 8,
                                bottomLeft: 8,
                                bottomRight: 8
                            },
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false,
                            },
                            border: {
                                display: false,
                            },
                            ticks: {
                                font: {
                                    size: 14,
                                    weight: "400",
                                },
                                padding: 10,
                            },
                        },
                        y: {
                            stacked: true,
                            grid: {
                                color: "#E5E5EA",
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                stepSize: 50,
                                font: {
                                    family: "Poppins",
                                    size: 11,
                                    weight: "400",
                                },
                            },
                        },
                    },
                },
            };

            buildChart('audit-finding-chart', config);
        }

        function buildCriteriaChart(category = [], chartDataKetercapaian = [], chartDataBobotTarget = []) {
            const data = {
                labels: category,
                datasets: [{
                    label: 'Realisasi AMI',
                    data: chartDataKetercapaian,
                    fill: true,
                    backgroundColor: 'rgba(93, 136, 246, 0.3)',
                    borderColor: '#a4c0f9',
                    pointBackgroundColor: '#5d88f6',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#5d88f6'
                }, {
                    label: 'Target Capaian',
                    data: chartDataBobotTarget,
                    fill: true,
                    backgroundColor: 'rgb(235 168 113 / 15%)',
                    borderColor: '#eba871',
                    pointBackgroundColor: '#eba871',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#eba871'
                }]
            };

            const config = {
                type: 'radar',
                data: data,
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    elements: {
                        line: {
                            borderWidth: 3
                        }
                    },
                    scale: {
                        ticks: {
                            max: 100,
                            min: 0,
                            stepSize: 20,
                            beginAtZero: false,
                        },
                    },
                    scales: {
                        r: {
                            min: 0,
                            pointLabels: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest'
                    }
                },
            }

            buildChart('criteria-chart', config);
        }

        function buildChart(id, config) {
            const ctx = document.getElementById(id);

            // jika chart sudah ada, destroy dulu
            if (window[id + '#chart']) {
                window[id + '#chart'].destroy();
                delete window[id + '#chart'];
            }

            window[id + '#chart'] = new Chart(ctx, config);
        }
    </script>
@endpush
