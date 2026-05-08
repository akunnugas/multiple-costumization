@php
    use Modules\SPMI\Models\PenilaianMatriks;
    use Modules\SPMI\Models\PenilaianSkor;

    // default title
    $title = 'Pengisian Penilaian Auditor';
    if (empty($subtitle) && !empty($title)) {
        $subtitle = $title;
    }
    $userRole = auth()->user()->kode_role;
    $listDisableEditSkorPanduan = \Modules\SPMI\Models\PenilaianPanduan::DISABLE_EDIT_SKOR_PANDUAN;
@endphp

@push('head')
    @vite('Modules/SPMI/Resources/assets/sass/penilaian-audit/create.scss')
    @vite('resources/scss/layouts/_detail.scss')
@endpush

<x-core::form id="penilaian-form">
    <div class="full-page-loader" wire:loading.flex>
        <div class="loader">
            <span class="loader__spinner"></span>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <div class="card__header">
                <ul class="breadcrumb">
                    <li class="breadcrumb__item">
                        <span class="icon icon-home-mini"></span>
                    </li>
                    @foreach ($breadcrumb['items'] as $i => $item)
                        @if ($item['showLink'])
                            <li class="breadcrumb__item">
                                <a href="{{ url($item['path']) }}">
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @elseif (!empty($item['label']))
                            <li class="breadcrumb__item active">{{ $item['label'] }}</li>
                        @endif
                    @endforeach
                    @if ($breadcrumb['showTitle'])
                        <li class="breadcrumb__item active">{{ $subtitle }}</li>
                    @endif
                </ul>

                <div class="button-group">
                    <div class="button-group__left">
                        <a class="btn btn_outline btn_xs" href="{{ route('spmi.penilaian-auditor.index') }}">
                            Kembali ke List
                        </a>

                        <button type="button" class="btn btn_outline btn_xs" wire:click="exportExcel">
                            Export Excel
                        </button>

                        @if ($isCanAction && $userRole !== \Modules\Gate\Models\Role::ROLE_AUDITEE)
                            @if ($isInternalRole && $isFinalized)
                                <button type="button" class="btn btn_outline btn_xs" data-toggle="modal"
                                    data-target="#modal-confirmation-recalculate">
                                    Hitung Ulang Skor
                                </button>
                            @endif
                            <button type="button" class="btn btn_primary btn_xs" data-toggle="modal"
                                data-target="#modal-confirmation-finalize"
                                {{ $isShowScoreForm || !$isValidAssessmentDate || $isNotFinalizeAuditeeAssessment || $isNotFinalizedTargetIndikator ? 'disabled' : null }}>
                                {{ $isFinalized ? 'Batalkan Finalisasi Data' : 'Finalisasi Data' }}
                            </button>

                        @endif
                    </div>

                    <div class="button-group__mobile">
                        <a class="btn btn_outline btn_icon btn_xs" href="{{ route('spmi.penilaian-auditor.index') }}">
                            <span class="icon icon-arrow-left-solid"></span>
                        </a>

                        {{-- FIXME: Belum dibutuhkan button responsive --}}
                        {{-- <button type="button" class="btn btn_primary btn_icon btn_xs">
                            <span class="icon icon-pencil-solid"></span>
                        </button> --}}
                    </div>
                </div>
            </div>

            <div class="card__body">
                <div class="card_table">
                    <div class="card__header" wire:ignore.self>
                        <div class="legend" wire:ignore.self>
                            @foreach ($information as $key => $value)
                                @php
                                    $label = Page::defineLabelByField($key);
                                    if ($key == 'nilai_akhir') {
                                        $label = 'Skor Akhir AMI'; // Edge case untuk Livewire
                                    }
                                    $isMultiLine = Str::contains($value, '<br>');
                                    $lines = explode('<br>', $value);
                                @endphp

                                <div class="legend__section" wire:ignore.self @if($isMultiLine) style="align-items: flex-start !important;" @endif>
                                    <div class="legend__section_left" wire:ignore>
                                        <h3>{{ $label }}</h3>
                                    </div>
                                    <span>:</span>
                                    <div class="legend__section_right" wire:ignore.self>
                                        @if ($isMultiLine && count($lines) > 2)
                                            <div x-data="{ showAll: false }">
                                                <p>
                                                    {!! implode('<br>', array_slice($lines, 0, 1)) !!}
                                                    <span x-show="showAll">
                                                        <br>{!! implode('<br>', array_slice($lines, 1)) !!}
                                                    </span>
                                                </p>
                                                <a href="#" @click.prevent="showAll = !showAll" style="color: #0f6af5;">
                                                    <span x-text="showAll ? 'Lihat Sedikit' : 'Lihat Selengkapnya'"></span>
                                                </a>
                                            </div>
                                        @else
                                            <p>{!! $value !!}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card__body">
                        <div class="box-table">
                            <div class="box-table__content">
                                <x-core::layouts.html.alert style="margin-bottom:1rem" :data="$alert ?? null" />

                                {{-- Jika tanggal penilaian tidak valid --}}
                                @if (!$isValidAssessmentDate)
                                    @php
                                        $isBeforeAssessmentStart = \Carbon\Carbon::now()->lt(
                                            \Carbon\Carbon::parse($raw['tanggal_awal_penilaian']),
                                        );
                                    @endphp
                                    <div class="alert alert_{{ $isBeforeAssessmentStart ? 'warning' : 'danger' }}"
                                        style="margin-bottom: 1rem">
                                        <div class="alert__content">
                                            <p>Periode penilaian tanggal <b>{{ $information['tanggal_penilaian'] }}</b>
                                                {{ $isBeforeAssessmentStart
                                                    ? 'belum dibuka. Anda belum bisa melakukan penilaian.'
                                                    : ' sudah terlewat. Anda tidak bisa melakukan penilaian lagi.' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Jika penilaian mandiri belum difinalisasi --}}
                                @elseif ($isNotFinalizeAuditeeAssessment)
                                    <div class="alert alert_warning" style="margin-bottom: 1rem">
                                        <div class="alert__content">
                                            <p>Penilaian mandiri oleh auditee belum difinalisasi. Anda tidak bisa
                                                melakukan
                                                penilaian auditor sebelum penilaian mandiri selesai.</p>
                                        </div>
                                    </div>
                                    {{-- Jika belum ada target capaian --}}
                                @elseif ($isNotFinalizedTargetIndikator)
                                    <div class="alert alert_warning" style="margin-bottom: 1rem">
                                        <div class="alert__content">
                                            <p>Mohon untuk mengatur dan memfinalisasi target capaian terlebih dahulu. <a
                                                    class="link"
                                                    href="{{ route('spmi.target-indikator.index') }}">Atur
                                                    target capaian</a>
                                            </p>
                                        </div>
                                    </div>
                                @elseif ($isFinalized)
                                    <div class="alert alert_helper" style="margin-bottom: 1rem">
                                        <div class="alert__content">
                                            <p>Penilaian sudah difinalisasi. Anda tidak bisa melakukan perubahan lagi.
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $totalMatrixIndicator = count(
                                            array_filter(
                                                $assessmentMatrices,
                                                fn($item) => $item->kategori_penilaian ==
                                                    PenilaianMatriks::CATEGORY_INDICATOR,
                                            ),
                                        );
                                        $totalEmptyMatrixIndicator = count(
                                            array_filter(
                                                $assessmentMatrices,
                                                fn($item) => $item->kategori_penilaian ==
                                                    PenilaianMatriks::CATEGORY_INDICATOR && empty($item->nilai_auditor),
                                            ),
                                        );
                                    @endphp
                                    <div class="alert alert_{{ $totalEmptyMatrixIndicator == 0 ? 'success' : 'warning' }}"
                                        style="margin-bottom: 1rem">
                                        <div class="alert__content">
                                            @if ($totalEmptyMatrixIndicator == 0)
                                                <p>{{ $totalMatrixIndicator }} dari {{ $totalMatrixIndicator }}
                                                    penilaian
                                                    auditor
                                                    sudah terisi.</p>
                                            @else
                                                <p>{{ $totalEmptyMatrixIndicator }} dari {{ $totalMatrixIndicator }}
                                                    penilaian
                                                    auditor
                                                    belum terisi.</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <div class="filter-section">
                                    <div class="search-bar">
                                        <div class="form-control">
                                            <div class="form-control__group">
                                                <span data-input-icon="search"></span>
                                                <input class="form-control__input" type="search" value=""
                                                    placeholder="Cari data penetapan indikator..."
                                                    wire:model.live.debounce.1000ms="search">
                                                <span data-clear="input"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="filter-bar kategori-pengisian">
                                        <x-core::controls.select label="Kategori Penilaian" purpose="filter"
                                            wire:change="updateFilter($event.target.value, 0)" :options="[
                                                PenilaianMatriks::TYPE_QUALITATIVE => 'Kualitatif',
                                                PenilaianMatriks::TYPE_QUANTITATIVE => 'Kuantitatif',
                                                PenilaianMatriks::TYPE_MIXED => 'Gabungan (Kuantitatif & Kualitatif)',
                                            ]"
                                            :value="$filterCategory" :selected="empty($filterCategory) ? 'all' : $filterCategory" />
                                    </div>
                                    <div class="filter-bar status-pengisian">
                                        <x-core::controls.select label="Status Pengisian" purpose="filter"
                                            wire:change="updateFilter($event.target.value, 1)" :options="['checked' => 'Terisi', 'unchecked' => 'Belum Terisi']"
                                            :value="$filterElement" :selected="empty($filterElement) ? 'all' : $filterElement" />
                                    </div>
                                </div>
                                <table class="table-data">
                                    <thead>
                                        <tr>
                                            <th style="width: 10px">
                                                No
                                            </th>
                                            <th>
                                                Elemen Dan Indikator
                                            </th>
                                            <th style="width: 10%">
                                                Kategori
                                            </th>
                                            <th style="width: 5%">
                                                Bobot
                                            </th>
                                            <th style="width: 5%">
                                                Target
                                            </th>
                                            <th style="width: 5%">
                                                Skor Auditee
                                            </th>
                                            <th style="width: 5%">
                                                Skor Auditor
                                            </th>
                                            <th style="width: 8%">
                                                Skor Akhir
                                            </th>
                                            <th style="width: 10%">
                                                Status
                                            </th>
                                            <th style="width: 10%">
                                                Feedback
                                            </th>
                                            @if ($isCanAction && $userRole !== \Modules\Gate\Models\Role::ROLE_AUDITEE)
                                                <th style="width: 5%">
                                                    Aksi
                                                </th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($filteredMatrices as $item)
                                            @php
                                                $item = (array) $item;
                                                $isElement =
                                                    $item['kategori_penilaian'] == PenilaianMatriks::CATEGORY_ELEMENT;
                                                $isIndicator =
                                                    $item['kategori_penilaian'] == PenilaianMatriks::CATEGORY_INDICATOR;
                                                $isDimension =
                                                    $item['kategori_penilaian'] == PenilaianMatriks::CATEGORY_DIMENSION;
                                                $isEdit = $isShowScoreForm && $idMatriksPenilaian == $item['id'];
                                            @endphp

                                            <tr @if (isset($item['id_parent']))  @endif @class([
                                                'selected_section top' => $isEdit,
                                            ])>
                                                @unless ($isElement)
                                                    <td>
                                                        {{ $item['nomor_penilaian'] }}
                                                    </td>
                                                @endunless

                                                <td @if ($isElement || $isDimension) colspan="100" style="background: #f9fafc" @endif
                                                    @class([
                                                        'align-top' => $isIndicator,
                                                    ])>
                                                    <p @style([
                                                        'margin-left: ' . 24 * $item['info_level'] . 'px' => $isElement,
                                                    ])>
                                                        @if ($isElement || $isDimension)
                                                            <b>{{ strip_tags($item['pertanyaan_penilaian']) }}</b>
                                                        @else
                                                            {{ strip_tags($item['pertanyaan_penilaian']) }}
                                                        @endif
                                                    </p>
                                                </td>

                                                @if ($isIndicator)
                                                    {{-- Jika Indikator --}}
                                                    <td>
                                                        {{ PenilaianMatriks::TYPES[$item['jenis_penilaian']] }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $item['bobot_penilaian'] ?? 'NA' }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $item['nilai_target'] ?? 'NA' }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $item['nilai_auditee'] ?? 'NA' }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $item['nilai_auditor'] ?? 'NA' }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $item['nilai_akhir'] ?? 'NA' }}
                                                    </td>
                                                    <td>
                                                        <div class="util_d-flex util_flex-center util_w-100">
                                                            @php
                                                                $auditorStatus =
                                                                    $item['status_penilaian_auditor'] ?? null;

                                                                // NOTE: Sementara tidak menampilkan status secara realtime
                                                                // if (isset($editableStatus) && $isEdit) {
                                                                //     $auditorStatus = $editableStatus;
                                                                // }
                                                            @endphp
                                                            <p @class([
                                                                'color-success' => $auditorStatus == PenilaianSkor::STATUS_MELAMPAUI,
                                                                'color-warning' => $auditorStatus == PenilaianSkor::STATUS_MEMENUHI,
                                                                'color-danger' =>
                                                                    $auditorStatus == PenilaianSkor::STATUS_BELUM_MEMENUHI ||
                                                                    $auditorStatus == PenilaianSkor::STATUS_MENYIMPANG,
                                                            ])>
                                                                {{ PenilaianSkor::STATUS[$auditorStatus] ?? 'Belum Diisi' }}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="util_d-flex util_flex-middle gap-8">
                                                            @if (!empty($item['catatan_penilaian']))
                                                                <button class="btn btn_outline btn_xs" type="button"
                                                                    data-toggle="modal" data-target="#feedback-modal"
                                                                    data-feedback="{{ $item['catatan_penilaian'] }}"
                                                                    style="border-color: #0F6AF5;">
                                                                    Ada Feedback
                                                                </button>
                                                            @else
                                                                <span>
                                                                    Tidak Ada
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    @if ($isCanAction && $userRole !== \Modules\Gate\Models\Role::ROLE_AUDITEE)
                                                        <td>
                                                            @if (!$isEdit)
                                                                @php
                                                                    $disabled =
                                                                        !$isValidAssessmentDate ||
                                                                        $isNotFinalizedTargetIndikator ||
                                                                        $isNotFinalizeAuditeeAssessment ||
                                                                        $isFinalized;
                                                                @endphp
                                                                <x-core::button size="xs" :variant="!empty($item['nilai_akhir'])
                                                                    ? 'outline'
                                                                    : 'primary'"
                                                                    @style([
                                                                        'color: #fff' => empty($item['nilai_akhir']),
                                                                        'color: #466bec; font-weight: 500' => !empty($item['nilai_akhir']) && !$disabled,
                                                                        'border: 1px solid #466bec' => !empty($item['nilai_akhir']) && !$disabled,
                                                                    ]) :$disabled
                                                                    wire:click="{{ !$disabled ? 'showScoreForm(true, ' . $item['id'] . ')' : '' }}">
                                                                    {{ !empty($item['nilai_akhir']) ? 'Ubah Skor' : 'Isi Skor' }}
                                                                </x-core::button>
                                                            @else
                                                                <div class="selected_section__action">
                                                                    <x-core::button leading-icon="x-mark"
                                                                        variant="outline" size="xs"
                                                                        wire:click="showScoreForm(false)" />
                                                                    <x-core::button leading-icon="check"
                                                                        variant="primary" size="xs"
                                                                        wire:click="{{ isset($checkedScore) ? 'saveScore()' : null }}"
                                                                        :disabled="is_null($checkedScore)" />
                                                                </div>
                                                            @endif
                                                        </td>
                                                    @endif
                                                @endif
                                            </tr>

                                            {{-- Jika Tambah data --}}
                                            @if ($isEdit)
                                                @php
                                                    $isQuantitative = $item['jenis_penilaian'] == PenilaianMatriks::TYPE_QUANTITATIVE && !$keyPanduan['apakah_iku_kualitatif'];
                                                @endphp
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <b>Pilih Skor Penilaian</b>
                                                    </td>
                                                </tr>
                                                <tr class="selected_section">
                                                    <td colspan="100" class="score-td">
                                                        <div class="score-section">
                                                            @foreach ($listPenilaianMatriksSkor as $idx => $skorItem)
                                                                @php
                                                                    $penilaianMatriksSkor = (array) $skorItem;
                                                                    $score = $penilaianMatriksSkor['nilai'] ?? null;
                                                                    $isDisable =
                                                                        $penilaianMatriksSkor['apakah_nonaktif'] ??
                                                                        false;
                                                                    $isEndDisable =
                                                                        $penilaianMatriksSkor['end_disable'] ?? false;
                                                                    $colspan = $penilaianMatriksSkor['colspan'] ?? 0;
                                                                    $disableScore =
                                                                        $penilaianMatriksSkor['disable_score'] ?? [];

                                                                    if ($score !== null) {
                                                                        $betweenMinScore =
                                                                            $penilaianMatriksSkor['nilai'] - 1;
                                                                        $betweenMinScore =
                                                                            $betweenMinScore > 0 ? $betweenMinScore : 0;
                                                                        $betweenMaxScore = $score + $colspan;
                                                                        $betweenMaxScore =
                                                                            $betweenMaxScore >= 4
                                                                                ? 4
                                                                                : $betweenMaxScore;
                                                                    }
                                                                @endphp

                                                                @if ($isDisable && $isEndDisable)
                                                                    <div class="score-section__empty-item"
                                                                        @style([
                                                                            "width: calc(20% * {$colspan}) !important" => $colspan > 1,
                                                                        ])>
                                                                        @if (isset($betweenMaxScore, $betweenMinScore))
                                                                            {{ 'Tidak ada skor antara ' . $betweenMaxScore . ' dan ' . $betweenMinScore }}
                                                                        @else
                                                                            {{ 'Tidak ada skor' }}
                                                                        @endif
                                                                    </div>

                                                                    @continue
                                                                @endif

                                                                @if (in_array($score, $disableScore) && count($disableScore) <= 1)
                                                                    <div class="score-section__empty-item">
                                                                    </div>

                                                                    @continue
                                                                @endif

                                                                {{-- Jika skor IKT kosong --}}
                                                                @if ((empty($penilaianMatriksSkor) || $isDisable) && $item['apakah_data_default'] === false)
                                                                    <div class="score-section__empty-item">
                                                                        {{ 'Tidak ada skor ' . $score }}
                                                                    </div>
                                                                    @continue
                                                                @endif

                                                                {{-- Jika skor IKU kosong --}}
                                                                @if ((empty($penilaianMatriksSkor) || $isDisable) && $item['apakah_data_default'] === true)
                                                                    @continue
                                                                @endif

                                                                @php
                                                                    $checked = $score == $checkedScore;

                                                                    if (!isset($checkedScore)) {
                                                                        $checked = false;
                                                                    }

                                                                    $isError = $errors?->has('record.nilai');
                                                                    if ($isError) {
                                                                        $helper = $errors->first('record.nilai');
                                                                    }
                                                                @endphp

                                                                <div @class(['score-section__item', 'selected' => $checked])>
                                                                    <div class="flex-score">
                                                                        <div class="set-score">
                                                                            <x-core::checkbox
                                                                                id="checkbox_{{ $key }}_{{ $score }}"
                                                                                value="{{ $score }}"
                                                                                label="Skor {{ $score }}"
                                                                                wire:model="record.nilai_default.{{ $score }}"
                                                                                wire:change="onCheckedScore({{ $penilaianMatriksSkor['id'] }})"
                                                                                :disabled="$isQuantitative" />
                                                                        </div>
                                                                        @if ($checked)
                                                                            <div class="score-input form-control visible"
                                                                                style="margin-bottom: 8px">
                                                                                <div class="form-control__group">
                                                                                    <input class="form-control__input"
                                                                                        type="number"
                                                                                        id="custom-score-input"
                                                                                        data-value="{{ $score }}.00"
                                                                                        value="{{ $savedScore['nilai'] ?? $score . '.00' }}"
                                                                                        min="{{ $score }}"
                                                                                        max="{{ $score + 0.99 }}"
                                                                                        onkeypress="if(this.value.length==4) return false;"
                                                                                        step="0.00"
                                                                                        wire:model="record.nilai"
                                                                                        {{ ($isQuantitative || (in_array($keyPanduan['kode_penilaian_panduan'], $listDisableEditSkorPanduan) && $keyPanduan['apakah_data_default'])) ? 'disabled' : '' }}>
                                                                                </div>

                                                                                @if ($isError)
                                                                                    <p class="error-validation"
                                                                                        style="color: #e84118; margin-top:8px">
                                                                                        {{ $helper }}
                                                                                    </p>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <div>
                                                                        <p class="description">
                                                                            {!! $penilaianMatriksSkor['deskripsi'] !!}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if ($isQuantitative)
                                                    <tr class="selected_section">
                                                        <td colspan="100">
                                                            <div class="section-inline-form feedback">
                                                                <div class="section-inline-form__label">
                                                                    <b>Keterangan</b>
                                                                    <span>:</span>
                                                                </div>
                                                                <div class="section-inline-form__reference">
                                                                    <p class="description">{!! $item['deskripsi'] !!}</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="selected_section">
                                                        <td colspan="100">
                                                            <div class="section-inline-form feedback">
                                                                <div class="section-inline-form__label">
                                                                    <b>Rincian Skor</b>
                                                                    <span>:</span>
                                                                </div>
                                                                <div class="section-inline-form__reference">
                                                                    <div class="grid util_grid_row_gap-0">
                                                                        @foreach ($quantitativeScores as $key => $quantitativeScore)
                                                                            <div class="col-4">
                                                                                <p>
                                                                                    <b>{{ $key }}</b>
                                                                                    <span>:</span>
                                                                                    {{ $quantitativeScore }}
                                                                                </p>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <div class="section-inline-form feedback">
                                                            <div class="section-inline-form__label">
                                                                <b>Bukti Referensi</b>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="section-inline-form__reference">
                                                                @foreach ($referenceIndicators as $referenceIndicator)
                                                                    @if (Str::contains($referenceIndicator['link'], '#'))
                                                                        <span style="color: #999; cursor: not-allowed;">{{ $referenceIndicator['name'] }}</span>
                                                                    @else
                                                                        <a target="_blank" rel="noreferer noopener"
                                                                            href="{{ $referenceIndicator['link'] }}">{{ $referenceIndicator['name'] }}</a>
                                                                    @endif

                                                                    @if (!$loop->last)
                                                                        &nbsp;&nbsp; , &nbsp;&nbsp;
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <div class="section-inline-form feedback">
                                                            <div class="section-inline-form__label">
                                                                <span><b>Status Penilaian</b></span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="section-inline-form__reference"
                                                                style="line-height: 14px;">
                                                                <x-core::select label="Status Penilaian"
                                                                    purpose="form" :options="$statusOptions"
                                                                    wire:model.change="editableStatus"
                                                                    :selected="$editableStatus ??
                                                                        $record['status_penilaian']" />
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="selected_section bottom">
                                                    <td colspan="100">
                                                        <div class="section-inline-form feedback">
                                                            <div class="section-inline-form__label">
                                                                <span><b>Feedback</b> (Optional)</span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="section-inline-form__reference">
                                                                <x-core::textarea class="feedback-input"
                                                                    label="Feedback"
                                                                    value="{{ $record['catatan_penilaian'] ?? null }}"
                                                                    wire:model="record.catatan_penilaian" />
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                                <x-core::handler id="no-data" :withHandleButton="false" :title="empty($filteredMatrices) && (!empty($filterElement) || !empty($search))
                                    ? 'Data tidak ditemukan'
                                    : 'Belum Ada Data'"
                                    :subtitle="empty($filteredMatrices) && (!empty($filterElement) || !empty($search))
                                        ? 'Data yang Anda cari tidak ditemukan, silahkan coba kata kunci lain dan filter yang lain'
                                        : 'Data indikator penilaian belum tersedia'" @style([
                                            'display: none' => !empty($filteredMatrices),
                                        ]) />
                            </div>
                            <div class="box-table__footer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-core::scroll-to-top />

    <x-core::modal title="Detail Feedback" variant="primary" id="feedback-modal">
        <x-core::modal.body>
            <p class="feedback-box"></p>
            <x-slot:footer>
                <div class="grid cols-1">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Oke
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::modal>

    <x-core::modal title="{{ $isFinalized ? 'Batalkan Finalisasi Data' : 'Finalisasi Data' }}" variant="primary"
        id="modal-confirmation-finalize">
        <x-core::modal.body>
            @if ($isFinalized)
                Apakah Anda yakin ingin membatalkan finalisasi data?
            @else
                Apakah Anda yakin ingin finalisasi data penilaian oleh auditor?
                Jika sudah difinalisasi, data tidak dapat diubah lagi.
            @endif
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batal
                    </x-core::button>
                    <x-core::button variant="primary"
                        wire:click="{{ $isFinalized ? 'unfinalizeData' : 'finalizeData' }}">
                        Oke
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::modal>

    {{-- Modal konfirmasi hitung ulang skor --}}
    @if ($isInternalRole && $isFinalized)
        <x-core::modal title="Hitung Ulang Skor" variant="primary" id="modal-confirmation-recalculate">
            <x-core::modal.body>
                Apakah Anda yakin ingin menghitung ulang skor penilaian?
                Proses ini membutuhkan waktu beberapa saat.
                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batal
                        </x-core::button>
                        <x-core::button variant="primary" wire:click='recalculateScore'>
                            Oke
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::modal>
    @endif

    @push('scripts')
        <script>
            const validateMinMax = (element) => {
                if (element.value === "") {
                    element.value = element.min;
                    return;
                }

                if (parseFloat(element.value) < parseFloat(element.min)) {
                    element.value = element.min;
                    return;
                }

                // Validasi jika min 4 dan value lebih dari 4, maka value = min
                if (parseFloat(element.min) === 4 && parseFloat(element.value) > 4) {
                    element.value = element.min;
                    return;
                }

                if (parseFloat(element.value) > parseFloat(element.max)) {
                    element.value = element.max;
                    return;
                }
            };

            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('element.init', ({
                    el
                }) => {
                    if (el.id == 'custom-score-input') {
                        el.addEventListener('input', (event) => {
                            const element = event.target;
                            validateMinMax(element);
                        });
                    }
                });

                Livewire.on('scroll-to-active', () => {
                    setTimeout(() => {
                        document.querySelector(".selected_section").scrollIntoView();
                    }, 250);
                });

                document.querySelector('span[data-clear="input"]').addEventListener('click', (event) => {
                    Livewire.dispatch('updatedSearch');
                });
            });

            document.querySelector('body').addEventListener('click', (event) => {
                const target = event.target;
                const feedbackBtn = target.closest('.btn_outline');

                if (feedbackBtn) {
                    const feedback = feedbackBtn.dataset.feedback;
                    const feedbackBox = document.querySelector('.feedback-box');
                    feedbackBox.innerHTML = feedback || 'Tidak ada feedback';
                }
            });

            document.querySelector('#penilaian-form').addEventListener('submit', (event) => {
                event.preventDefault();
            });
        </script>
    @endpush
</x-core::form>
