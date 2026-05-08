@php
    use Modules\SPMI\Models\PenilaianMatriks;
    use Modules\SPMI\Models\PenilaianSkor;
    use Carbon\Carbon;

    // default title
    $title = 'Pengisian Data RTM';
    if (empty($subtitle) && !empty($title)) {
        $subtitle = $title;
    }
@endphp

@push('head')
    @vite('Modules/SPMI/Resources/assets/sass/tinjauan-temuan/create.scss')
    @vite('resources/scss/layouts/_detail.scss')
@endpush

<x-core::form>
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
                        <a class="btn btn_outline btn_xs" href="{{ route('spmi.tinjauan-temuan.index') }}">
                            Kembali ke List
                        </a>

                        {{-- // TODO: TEMPORARY HIDDEN, PERLU DISESUAIKAN MELIHAT MULTI JADWAL AUDIT --}}
                        {{-- <button type="button" class="btn btn_primary btn_xs" data-toggle="modal"
                            data-target="#modal-confirmation-sync-next-target"
                            {{ !$resourceId || !$isFinishAssessment || !$isFinalizeTemuan || !$isCanAction || $isShowForm ? 'disabled' : null }}>
                            Terapkan Target
                        </button> --}}
                    </div>

                    <div class="button-group__mobile">
                        <button type="button" class="btn btn_outline btn_icon btn_xs"
                            href="{{ route('spmi.tinjauan-temuan.index') }}">
                            <span class="icon icon-arrow-left-solid"></span>
                        </button>
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

                                <div
                                    class="alert alert alert_{{ !$isFinishAssessment || !$isFinalizeTemuan ? 'warning' : 'helper' }} util_mb-12">
                                    <div class="alert__content">
                                        @if (!$isFinishAssessment)
                                            <h4 class="alert__heading">Penilaian belum diselesaikan</h4>
                                            <p>
                                                Silakan menyelesaikan penilaian oleh auditor terlebih dahulu untuk dapat
                                                melakukan pengisian temuan.
                                            </p>
                                        @elseif (!$isFinalizeTemuan)
                                            <h4 class="alert__heading">Temuan auditor belum difinalisasi</h4>
                                            <p>
                                                Silakan finalisasi temuan auditor terlebih dahulu untuk dapat melakukan
                                                pengisian data RTM.
                                            </p>
                                        @else
                                            <h4 class="alert__heading">Periksa RTM</h4>
                                            <p>
                                                Pastikan periode AMI, mapping matriks penilaian, dan jadwal AMI tahun berikutnya telah disiapkan sebelum menetapkan target capaian.
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="filter-section">
                                    <div class="search-bar">
                                        <div class="form-control">
                                            <div class="form-control__group">
                                                <span data-input-icon="search"></span>
                                                <input class="form-control__input" type="search" value=""
                                                    placeholder="Cari data penetapan indikator..." id="search-input"
                                                    wire:model.live.debounce.1000ms="search">
                                                <span id="clear-search" data-clear="input"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="filter-bar kategori-pengisian">
                                        <x-core::controls.select label="Kategori Penilaian" purpose="filter"
                                            wire:change="updateFilter($event.target.value, 0)" :options="[
                                                PenilaianMatriks::TYPE_QUALITATIVE => 'Kualitatif',
                                                PenilaianMatriks::TYPE_QUANTITATIVE => 'Kuantitatif',
                                                PenilaianMatriks::TYPE_MIXED => PenilaianMatriks::TYPES[PenilaianMatriks::TYPE_MIXED]
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
                                            <th style="width: 5%;">
                                                Target
                                            </th>
                                            <th style="width: 5%;">
                                                Capaian
                                            </th>
                                            <th style="width: 20%">
                                                Temuan
                                            </th>
                                            <th style="width: 20%">
                                                Rencana Tindak Lanjut
                                            </th>
                                            <th style="width: 10%">
                                                Pelaksana
                                            </th>
                                            <th style="width: 8%">
                                                Tanggal Perbaikan
                                            </th>
                                            <th style="width: 8%">
                                                Jenis Temuan
                                            </th>
                                            @if ($isCanAction)
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

                                                $countEmptyScore = 5 - count($listPenilaianMatriksSkor);
                                                $isQuantitative =
                                                    $item['jenis_penilaian'] == PenilaianMatriks::TYPE_QUANTITATIVE;

                                                $isEdit = $isShowForm && $penilaianMatriksId == $item['id'];
                                            @endphp

                                            <tr @if (isset($item['id_parent']))  @endif @class([
                                                'selected_section top' => $isEdit,
                                            ])>
                                                @unless ($isElement)
                                                    <td class="align-top">
                                                        {{ $item['nomor_penilaian'] }}
                                                    </td>
                                                @endunless

                                                <td @if ($isElement || $isDimension) colspan="100" style="background: #f9fafc" @endif
                                                    class="align-top">
                                                    <p @style([
                                                        'margin-left: ' . 24 * $item['info_level'] . 'px' => $isElement,
                                                    ])>
                                                        @if ($isElement || $isDimension)
                                                            <b>{{ strip_tags($item['pertanyaan_penilaian']) }}</b>
                                                        @else
                                                            {{ strip_tags($item['pertanyaan_penilaian']) }}
                                                        @endif
                                                    </p>

                                                    @if ($isIndicator)
                                                        @php
                                                            $isStatusBerulang = $item['apakah_temuan_berulang'];
                                                            $badgeState = $isStatusBerulang ? 'danger' : 'success';
                                                        @endphp
                                                        <span class="badge badge_secondary-{{ $badgeState }}"
                                                            style="margin-top: 8px">
                                                            {{ $isStatusBerulang ? 'Berulang' : 'Tidak Berulang' }}
                                                        </span>
                                                    @endif
                                                </td>

                                                @if ($isIndicator)
                                                    {{-- Jika Indikator --}}
                                                    <td class="align-top" style="text-align: right">
                                                        {{ $item['nilai_target'] ?? 'NA' }}
                                                    </td>
                                                    <td class="align-top" style="text-align: right">
                                                        {{ $item['nilai_auditor'] ?? 'NA' }}
                                                    </td>
                                                    <td class="align-top">
                                                        {{ $item['uraian_temuan_audit'] ?? 'Belum ada (-)' }}
                                                    </td>
                                                    <td class="align-top">
                                                        {{ $item['rencana_peningkatan_mutu'] ?? 'Belum ada (-)' }}
                                                    </td>
                                                    <td class="align-top">
                                                        {{ $item['pelaksana'] ?? 'Belum ada (-)' }}
                                                    </td>
                                                    <td class="align-top">
                                                        {{ !empty($item['tanggal_peningkatan_mutu']) ? Carbon::parse($item['tanggal_peningkatan_mutu'])->translatedFormat('d F Y') : 'Belum ada (-)' }}
                                                    </td>
                                                    <td class="align-top">
                                                        {{ $findingTypeOptions[$item['jenis_temuan']] ?? 'Belum ada (-)' }}
                                                    </td>
                                                    @if ($isCanAction)
                                                        <td class="align-top">
                                                            @if (!$isEdit)
                                                                @php
                                                                    $disabled =
                                                                        !$isFinishAssessment || !$isFinalizeTemuan;
                                                                @endphp
                                                                <x-core::button size="xs" :variant="!empty($item['id_tinjauan_temuan'])
                                                                    ? 'outline'
                                                                    : 'primary'"
                                                                    @style([
                                                                        'color: #fff' => empty($item['id_tinjauan_temuan']),
                                                                        'color: #466bec; font-weight: 500' => !empty($item['id_tinjauan_temuan']) && !$disabled,
                                                                        'border: 1px solid #466bec' => !empty($item['id_tinjauan_temuan']) && !$disabled,
                                                                    ]) :$disabled
                                                                    wire:click="{{ !$disabled ? 'showForm(true, ' . $item['id'] . ')' : '' }}">
                                                                    {{ !empty($item['id_tinjauan_temuan']) ? 'Ubah Data' : 'Isi Data' }}
                                                                </x-core::button>
                                                            @else
                                                                <div class="selected_section__action">
                                                                    <x-core::button leading-icon="x-mark"
                                                                        variant="outline" size="xs"
                                                                        wire:click="showForm(false)" />
                                                                    <x-core::button leading-icon="check"
                                                                        variant="primary" size="xs"
                                                                        wire:click="{{ isset($checkedScore) ? 'saveTinjauanTemuan()' : null }}"
                                                                        :disabled="is_null($checkedScore)" />
                                                                </div>
                                                            @endif
                                                        </td>
                                                    @endif
                                                @endif
                                            </tr>

                                            @if ($isEdit)
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <b>Pilih Skor Target:</b>
                                                    </td>
                                                </tr>

                                                <tr class="selected_section">
                                                    <td colspan="100" class="score-td">
                                                        <div class="score-section">
                                                            @for ($i = 4; $i >= 0; $i--)
                                                                @php
                                                                    $penilaianMatriksSkor = array_filter(
                                                                        $listPenilaianMatriksSkor,
                                                                        fn($item) => $item->nilai == $i,
                                                                    );
                                                                    $penilaianMatriksSkor =
                                                                        array_values($penilaianMatriksSkor)[0] ?? [];
                                                                    $penilaianMatriksSkor = (array) $penilaianMatriksSkor;
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
                                                                        $betweenMaxScore = $i + $colspan;
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

                                                                @if (in_array($i, $disableScore) && count($disableScore) <= 1)
                                                                    <div class="score-section__empty-item">
                                                                    </div>

                                                                    @continue
                                                                @endif

                                                                {{-- Jika skor IKT kosong --}}
                                                                @if ((empty($penilaianMatriksSkor) || $isDisable) && $item['apakah_data_default'] === false)
                                                                    <div class="score-section__empty-item">
                                                                        {{ 'Tidak ada skor ' . $i }}
                                                                    </div>
                                                                    @continue
                                                                @endif

                                                                {{-- Jika skor IKU kosong --}}
                                                                @if ((empty($penilaianMatriksSkor) || $isDisable) && $item['apakah_data_default'] === true)
                                                                    @continue
                                                                @endif

                                                                @php
                                                                    $penilaianMatriksSkor = (array) $penilaianMatriksSkor;
                                                                    $checked = $score == $checkedScore;

                                                                    if (!isset($checkedScore)) {
                                                                        $checked = false;
                                                                    }
                                                                @endphp

                                                                <div @class(['score-section__item', 'selected' => $checked])>
                                                                    <div class="flex-score">
                                                                        <div class="set-score">
                                                                            <x-core::checkbox
                                                                                id="checkbox_{{ $key }}_{{ $i }}"
                                                                                value="{{ $score }}"
                                                                                label="Skor {{ $score }}"
                                                                                wire:model="record.nilai_target_default.{{ $score }}"
                                                                                wire:change="onCheckedScore({{ $penilaianMatriksSkor['id'] }})" />
                                                                        </div>
                                                                        @if ($checked)
                                                                            <div class="score-input form-control visible"
                                                                                style="margin-bottom: 8px">
                                                                                <div class="form-control__group">
                                                                                    <input class="form-control__input"
                                                                                        type="number"
                                                                                        id="custom-score-input"
                                                                                        data-value="{{ $score }}.00"
                                                                                        value="{{ $record['nilai_target'] ?? $score . '.00' }}"
                                                                                        min="{{ $score }}"
                                                                                        max="{{ $score + 0.99 }}"
                                                                                        onkeypress="if(this.value.length==4) return false;"
                                                                                        step="0.00"
                                                                                        wire:model="record.nilai_target">
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <div>
                                                                        <p class="description">
                                                                            {!! $penilaianMatriksSkor['deskripsi'] !!}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            @endfor
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if ($errors->first('record.nilai_target'))
                                                    <tr class="selected_section">
                                                        <td colspan="100">
                                                            <p class="error-validation">
                                                                {{ $errors->first('record.nilai_target') }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <div class="section-inline-form textarea">
                                                            <div class="section-inline-form__label">
                                                                <span>
                                                                    <b>Akar Masalah</b><span class="important">*</span>
                                                                </span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="util_w-100">
                                                                <x-core::textarea class="textarea-input"
                                                                    label="Akar Masalah"
                                                                    value="{{ $record['akar_masalah'] ?? null }}"
                                                                    wire:model="record.akar_masalah" />
                                                                <p class="error-validation">
                                                                    {{ $errors->first('record.akar_masalah') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <div class="section-inline-form textarea">
                                                            <div class="section-inline-form__label">
                                                                <span>
                                                                    <b>Rencana Tindak Lanjut</b><span
                                                                        class="important">*</span>
                                                                </span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="util_w-100">
                                                                <x-core::textarea class="textarea-input"
                                                                    label="Rencana Tindak Lanjut"
                                                                    value="{{ $record['rencana_peningkatan_mutu'] ?? null }}"
                                                                    wire:model="record.rencana_peningkatan_mutu" />
                                                                <p class="error-validation">
                                                                    {{ $errors->first('record.rencana_peningkatan_mutu') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <div class="section-inline-form">
                                                            <div class="section-inline-form__label">
                                                                <span>
                                                                    <b>Pelaksana</b><span class="important">*</span>
                                                                </span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="util_w-100">
                                                                <div class="form-control">
                                                                    <div class="form-control__group">
                                                                        <x-core::input
                                                                            placeholder="Silakan masukkan Pelaksana"
                                                                            type="text"
                                                                            value="{{ $record['pelaksana'] ?? null }}"
                                                                            wire:model="record.pelaksana" />
                                                                    </div>
                                                                </div>
                                                                <p class="error-validation">
                                                                    {{ $errors->first('record.pelaksana') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="selected_section bottom">
                                                    <td colspan="100">
                                                        <div class="section-inline-form">
                                                            <div class="section-inline-form__label">
                                                                <span>
                                                                    <b>Tanggal Perbaikan</b><span
                                                                        class="important">*</span>
                                                                </span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="util_w-100">
                                                                <div class="form-control">
                                                                    <div class="form-control__group">
                                                                        <x-core::input type="date"
                                                                            value="{{ $record['tanggal_peningkatan_mutu'] ?? null }}"
                                                                            wire:model="record.tanggal_peningkatan_mutu" />
                                                                    </div>
                                                                </div>
                                                                <p class="error-validation">
                                                                    {{ $errors->first('record.tanggal_peningkatan_mutu') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                                <x-core::handler id="no-data" :title="empty($filteredMatrices) && !empty($filterElement)
                                    ? 'Data tidak ditemukan'
                                    : 'Belum Ada Data'" :subtitle="empty($filteredMatrices) && !empty($filterElement)
                                    ? 'Data yang Anda cari tidak ditemukan, silahkan coba kata kunci lain dan filter yang lain'
                                    : 'Data indikator temuan belum tersedia'" @style([
                                        'display: none' => !empty($filteredMatrices),
                                    ])
                                    :canCreate="false" />
                            </div>
                            <div class="box-table__footer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-core::modal title="Tetapkan sebagai target di periode {{ $information['periode_audit'] + 1 }}"
        variant="primary" id="modal-confirmation-sync-next-target">
        <x-core::modal.body>
            Apakah Anda yakin ingin menerapkan data RTM sebagai target indikator di periode
            <b>{{ $information['periode_audit'] + 1 }}</b>?
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batal
                    </x-core::button>
                    <x-core::button variant="primary" wire:click="syncNextTarget">
                        Oke
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::modal>

    <x-core::scroll-to-top />

    @push('scripts')
        <script>
            document.getElementById('search-input').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });

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
            });


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
        </script>
    @endpush
</x-core::form>
