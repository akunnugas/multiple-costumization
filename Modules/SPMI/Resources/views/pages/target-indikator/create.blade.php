@php
    use Modules\SPMI\Models\PenilaianMatriks;

    // default title
    $title = 'Pengisian Target Capaian';
    if (empty($subtitle) && !empty($title)) {
        $subtitle = $title;
    }

    $isCreate = empty($resourceId);

    $totalIndicator = 0;
    foreach ($assessmentMatricesRaw as $key => $item) {
        $item = (array) $item;
        if ($item['kategori_penilaian'] == PenilaianMatriks::CATEGORY_INDICATOR) {
            $totalIndicator++;
        }
    }

    $isDisableInput = $isFinalized || !$isCanAction;

    $listDisableEditSkorPanduan = \Modules\SPMI\Models\PenilaianPanduan::DISABLE_EDIT_SKOR_PANDUAN;
@endphp

@push('head')
    @vite('Modules/SPMI/Resources/assets/sass/target-indikator/create.scss')
    @vite('resources/scss/layouts/_detail.scss')
@endpush

<x-core::form onsubmit="event.preventDefault();">
    <div class="full-page-loader" wire:loading.flex wire:target="updateScoreValue,updateAllScoreValue">
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
                        <a class="btn btn_outline btn_xs" href="{{ route('spmi.target-indikator.index') }}">
                            Kembali ke List
                        </a>

                        <button type="button" class="btn btn_primary btn_xs" data-toggle="modal"
                            data-target="#modal-confirmation-finalize"
                            {{ !($resourceId || $resourceTempId) || !$isCanAction ? 'disabled' : null }}>
                            {{ $isFinalized ? 'Batalkan Finalisasi Data' : 'Finalisasi Data' }}
                        </button>
                    </div>

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
                <div class="card_table">
                    <div class="card__header" wire:ignore>
                        <div class="legend" wire:ignore>
                            @foreach ($information as $key => $value)
                                @php
                                    $label = Page::defineLabelByField($key);
                                @endphp
                                <div class="legend__section">
                                    <div class="legend__section_left">
                                        <h3>{{ $label }}</h3>
                                    </div>
                                    <span>:</span>
                                    <div class="legend__section_right">
                                        <p>{{ $value }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card__body">
                        <div class="box-table">
                            <div class="box-table__content">
                                <div class="filter-section">
                                    <div class="search-bar">
                                        <div class="form-control">
                                            <div class="form-control__group">
                                                <span data-input-icon="search"></span>
                                                <input class="form-control__input" type="search" value=""
                                                    placeholder="Cari data penetapan target capaian..."
                                                    wire:model.live.debounce.1000ms="search">
                                                <span data-clear="input" wire:click="resetSearch"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="filter-bar">
                                        <x-core::controls.select label="Status Pengisian" purpose="filter"
                                            wire:model.change="filterElement" :options="['checked' => 'Terisi', 'unchecked' => 'Belum Terisi']" :value="$filterElement"
                                            :selected="empty($filterElement) ? 'all' : $filterElement" />
                                    </div>
                                </div>

                                <x-core::layouts.html.alert style="margin-bottom:1rem" :data="$alert ?? null" />

                                @if($isNotHaveMatriks)
                                    <div class="alert alert_warning" style="margin-bottom: 20px;">
                                        <div class="alert__content">
                                                <p>
                                                    Tidak ada matriks penilaian yang tersedia untuk periode dan unit ini. Silakan
                                                    mappingkan matriks penilaian pada halaman <a style="color: blue; text-decoration: underline;" href="{{ route('spmi.mapping-matriks-penilaian.index') }}">Mapping Matriks Penilaian</a>.
                                                </p>
                                        </div>
                                    </div>
                                @else
                                    @if ($isFinalized)
                                        <div class="alert alert_helper" style="margin-bottom: 20px;">
                                            <div class="alert__content">
                                                <p>Data penetapan target capaian ini telah difinalisasi. Anda tidak dapat
                                                    melakukan
                                                    perubahan pada data ini.</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert {{ count($scoreValues) == $totalIndicator ? 'alert_success' : 'alert_warning' }}"
                                            id="alert-progress" data-count="0" data-total="{{ $totalIndicator }}">
                                            <div class="alert__content">
                                                <p id="info-count">
                                                    @if (count($scoreValues) == $totalIndicator)
                                                        Semua indikator telah terisi
                                                    @else
                                                        {{ $totalIndicator - count($scoreValues) }} dari
                                                        {{ $totalIndicator }} indikator
                                                        target
                                                        belum
                                                        terisi
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <div class="table-max">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th style="width: 10px">
                                                    No
                                                </th>
                                                <th style="width: 25%;">
                                                    Elemen Dan Indikator
                                                </th>
                                                @foreach ($scorePredikatValues as $score => $label)
                                                    <th class="header-set-score">
                                                        <x-core::checkbox id="set_all_score_{{ $score }}" value="{{ $score }}"
                                                            label="Set semua skor {{ $score }}{{ isset($scorePredikatValues[$score]) ? ' (' . $scorePredikatValues[$score] . ')' : '' }}"
                                                            wire:change="updateAllScoreValue({{ $score }}, $event.target.checked)"
                                                            :checked="$isCheckedAll == ($score . '.00')" @style([
                                                                'display: none !important' => $isDisableInput,
                                                            ]) />
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody id="assessmentTableContainer">
                                            @foreach ($assessmentMatrices as $key => $item)
                                                @php
                                                    $item = (array) $item;
                                                    $colspanScoreLength = 5;
                                                    $isElement =
                                                        $item['kategori_penilaian'] ==
                                                        PenilaianMatriks::CATEGORY_ELEMENT;
                                                    $isIndicator =
                                                        $item['kategori_penilaian'] ==
                                                        PenilaianMatriks::CATEGORY_INDICATOR;
                                                    $isDimension =
                                                        $item['kategori_penilaian'] ==
                                                        PenilaianMatriks::CATEGORY_DIMENSION;
                                                    $skorIndikator = json_decode($item['skor_indikator']);
                                                    $skorIndikator = array_values(array_filter($skorIndikator));
                                                @endphp
                                                <tr data-element="{{ $item['kategori_penilaian'] }}"
                                                    data-id="{{ $item['id'] }}"
                                                    @if (isset($item['id_parent'])) data-parent="{{ $item['id_parent'] }}" @endif>
                                                    @unless ($isElement)
                                                        <td class="align-top">
                                                            {{ $item['nomor_penilaian'] }}
                                                        </td>
                                                    @endunless

                                                    @if ($isElement || $isDimension)
                                                        <td colspan="7" style="background: #f9fafc">
                                                            <p style="margin-left: {{ 24 * $item['info_level'] }}px;">
                                                                <b>{{ strip_tags($item['pertanyaan_penilaian']) }}</b>
                                                            </p>
                                                        </td>
                                                    @else
                                                        <td class="align-top">
                                                            <p>
                                                                {{ strip_tags($item['pertanyaan_penilaian']) }}
                                                            </p>
                                                        </td>
                                                    @endif

                                                    @if ($isIndicator)
                                                        @if (!empty($skorIndikator))
                                                            @foreach ($scorePredikatValues as $scoreKey => $scoreLabel)
                                                                @php
                                                                    $matchedScore = null;
                                                                    foreach ($skorIndikator as $score) {
                                                                        $score = (array) $score;
                                                                        if ($score['nilai'] == $scoreKey && !$score['apakah_nonaktif']) {
                                                                            $matchedScore = $score;
                                                                            break;
                                                                        }
                                                                    }
                                                                @endphp

                                                                @if ($matchedScore)
                                                                    @php
                                                                        $scoreValue =
                                                                            $scoreValues[
                                                                                $item['id'] . '/' . $matchedScore['id']
                                                                            ] ?? [];
                                                                        $checked =
                                                                            !empty($scoreValues) &&
                                                                            isset(
                                                                                $scoreValues[
                                                                                    $item['id'] . '/' . $matchedScore['id']
                                                                                ],
                                                                            );
                                                                    @endphp
                                                                    <td @class([
                                                                        'selected' => $checked && !isset($scoreValue['error']),
                                                                        'error' => $checked && isset($scoreValue['error']),
                                                                        'score-section',
                                                                        'align-top',
                                                                    ])>
                                                                        <div class="form-control">
                                                                            <div class="set-score">
                                                                                <div class="checkbox">
                                                                                    <input type="checkbox"
                                                                                        id="checkbox_{{ $item['id'] . '_' . $matchedScore['id'] }}"
                                                                                        class="set-score__checkbox"
                                                                                        value="{{ $matchedScore['nilai'] }}"
                                                                                        wire:change="updateScoreValue({{ $item['id'] }}, {{ $matchedScore['id'] }}, {{ $matchedScore['nilai'] }}, $event.target.checked)"
                                                                                        {{ $checked ? 'checked' : '' }}
                                                                                        @disabled($isDisableInput) />
                                                                                    <label
                                                                                        for="checkbox_{{ $item['id'] . '_' . $matchedScore['id'] }}"
                                                                                        class="form-control__label-checkbox">
                                                                                        Skor {{ $matchedScore['nilai'] }}
                                                                                    </label>
                                                                                    @if ($checked)
                                                                                        <div
                                                                                            class="score-input visible">
                                                                                            <div
                                                                                                class="form-control__group">
                                                                                                <input
                                                                                                    class="form-control__input score-input-custom"
                                                                                                    type="number"
                                                                                                    value="{{ $scoreValue['nilai'] ?? $matchedScore['nilai'] . '.00' }}"
                                                                                                    min="{{ $matchedScore['nilai'] }}"
                                                                                                    max="{{ $matchedScore['nilai'] + 0.99 }}"
                                                                                                    {{-- onkeypress="if(this.value.length==4) return false;" --}}
                                                                                                    style="text-align: right;"
                                                                                                    wire:change="updateScoreValue({{ $item['id'] }}, {{ $matchedScore['id'] }}, $event.target.value)"
                                                                                                    id="input_{{ $item['id'] . '_' . $matchedScore['id'] }}"
                                                                                                    @if ($isDisableInput || (in_array($item['kode_penilaian_panduan'], $listDisableEditSkorPanduan) && $item['apakah_panduan_default'])) disabled @endif>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            @if ($checked && isset($scoreValue['error']))
                                                                                <p class="error-validation"
                                                                                    style="color: #e84118; margin-top:8px">
                                                                                    {{ $scoreValue['error'] }}</p>
                                                                            @endif
                                                                        </div>
                                                                        {!! $matchedScore['deskripsi'] !!}
                                                                    </td>
                                                                @else
                                                                    <td class="content-center">
                                                                        Tidak Ada
                                                                    </td>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            @foreach ($scorePredikatValues as $scoreKey => $scoreLabel)
                                                                <td class="content-center">
                                                                    Tidak Ada
                                                                </td>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <x-core::handler id="no-data" title="Belum Ada Data" :withHandleButton="false"
                                    subtitle="Data indikator penilaian belum tersedia" @style([
                                        'display: none' => !empty($assessmentMatrices),
                                    ]) />
                            </div>
                            <div class="box-table__footer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-core::modal title="{{ $isFinalized ? 'Batalkan Finalisasi Data' : 'Finalisasi Data' }}" variant="primary"
        id="modal-confirmation-finalize">
        <x-core::modal.body>
            @if ($isFinalized)
                Apakah Anda yakin ingin membatalkan finalisasi data penetapan target capaian?
            @else
                Apakah Anda yakin ingin finalisasi data penetapan target capaian?
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

    <x-core::scroll-to-top />

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var container = document.getElementById('assessmentTableContainer');

            // first init
            var inputs = container.querySelectorAll('input:not([disabled])[id^="input_"]');
            inputs.forEach((element) => {
                element.addEventListener("keyup", (event) => {
                    const element = event.target;
                    validateMinMax(element);
                });

                element.addEventListener("keydown", (event) => {
                    const element = event.target;
                    validateMinMax(element);
                });
            });

            // scroll listener to load more
            window.addEventListener('scroll', function() {
                // load more when scroll to bottom
                if (window.scrollY + window.innerHeight >= container.offsetHeight) {
                    @this.call('loadMore');
                }
            });

            // re-render checkbox
            window.addEventListener('re-render-checkbox', event => {
                let checkbox = document.getElementById(event.detail[0].uncheck_id);
                if (checkbox) {
                    checkbox.checked = false;
                }

                let input = document.getElementById(event.detail[0].check_input_id);
            });

            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('element.init', ({
                    el
                }) => {
                    if (el.className == 'form-control__input score-input-custom') {
                        el.addEventListener('input', (event) => {
                            const element = event.target;
                            validateMinMax(element);
                        });
                    }
                });
            });

            // re-render score all checkbox
            window.addEventListener('re-render-scoreall-checkbox', event => {
                let checkboxes = document.querySelectorAll('input[id^="set_all_score_"]');

                // foreach disable all
                checkboxes.forEach((element) => {
                    element.checked = false;
                });

                // enable only one
                let checkbox = document.getElementById(event.detail[0].id);
                checkbox.checked = true;
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

</x-core::form>
