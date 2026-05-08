@php
    use Modules\SPMI\Models\PenilaianMatriks;
    use Modules\SPMI\Models\PenilaianSkor;
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    // default title
    $title = 'Temuan Auditor';
    if (empty($subtitle) && !empty($title)) {
        $subtitle = $title;
    }
@endphp

@push('head')
    @vite('Modules/SPMI/Resources/assets/sass/audit-temuan/create.scss')
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
                        <span wire:ignore>
                            @php
                                $routeName = Str::beforeLast(url(request()->path()), '/');
                            @endphp
                            <a class="btn btn_outline btn_xs" href="{{ $routeName }}">
                                Kembali ke List
                            </a>
                        </span>
                        <button type="button" class="btn btn_primary btn_xs" data-toggle="modal"
                            data-target="#modal-confirmation-finalize"
                            {{ !$resourceId || !$isCanAction || !$isFinishAssessment ? 'disabled' : null }}>
                            {{ $isFinalized ? 'Batalkan Finalisasi Data' : 'Finalisasi Data' }}
                        </button>
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
                                    class="alert alert alert_{{ !$isFinishAssessment ? 'warning' : 'helper' }} util_mb-12">
                                    <div class="alert__content">
                                        @if (!$isFinishAssessment)
                                            <h4 class="alert__heading">Penilaian belum diselesaikan</h4>
                                            <p>
                                                Silakan menyelesaikan penilaian oleh auditor terlebih dahulu untuk dapat
                                                melakukan pengisian temuan.
                                            </p>
                                        @elseif ($isFinalized)
                                            <p>
                                                Temuan auditor sudah difinalisasi. Anda tidak bisa melakukan perubahan
                                                lagi.
                                            </p>
                                        @else
                                            <h4 class="alert__heading">Periksa Temuan Auditor</h4>
                                            <p>
                                                Silakan cek Temuan Auditor. Jika ada Temuan yang kurang, klik ikon
                                                pensil
                                                pada
                                                kolom
                                                Aksi dan tambahkan temuannya, atau edit Temuan yang sudah ada.
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
                                                Rencana Perbaikan
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

                                                <td class="align-top"
                                                    @if ($isElement || $isDimension) colspan="100" style="background: #f9fafc" @endif>
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
                                                                    $disabled = !$isFinishAssessment || $isFinalized;
                                                                @endphp
                                                                <x-core::button size="xs" :variant="!empty($item['id_audit_temuan'])
                                                                    ? 'outline'
                                                                    : 'primary'"
                                                                    @style([
                                                                        'color: #fff' => empty($item['id_audit_temuan']),
                                                                        'color: #466bec; font-weight: 500' => !empty($item['id_audit_temuan']) && !$disabled,
                                                                        'border: 1px solid #466bec' => !empty($item['id_audit_temuan']) && !$disabled,
                                                                    ]) :$disabled
                                                                    wire:click="{{ !$disabled ? 'showForm(true, ' . $item['id'] . ')' : '' }}">
                                                                    {{ !empty($item['id_audit_temuan']) ? 'Ubah Data' : 'Isi Data' }}
                                                                </x-core::button>
                                                            @else
                                                                <div class="selected_section__action">
                                                                    <x-core::button leading-icon="x-mark"
                                                                        variant="outline" size="xs"
                                                                        wire:click="showForm(false)" />
                                                                    <x-core::button leading-icon="check"
                                                                        variant="primary" size="xs"
                                                                        wire:click="{{ 'saveFinding()' }}" />
                                                                </div>
                                                            @endif
                                                        </td>
                                                    @endif
                                                @endif
                                            </tr>

                                            @if ($isEdit)
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <b>Silakan lengkapi data berikut</b>
                                                    </td>
                                                </tr>
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <div class="section-inline-form textarea">
                                                            <div class="section-inline-form__label">
                                                                <span>
                                                                    <b>Temuan</b><span class="important">*</span>
                                                                </span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="util_w-100">
                                                                <x-core::textarea class="textarea-input"
                                                                    label="Temuan"
                                                                    value="{{ $record['uraian_temuan_audit'] ?? null }}"
                                                                    wire:model="record.uraian_temuan_audit" />
                                                                <p class="error-validation">
                                                                    {{ $errors->first('record.uraian_temuan_audit') }}
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
                                                                    <b>Rencana Perbaikan</b><span
                                                                        class="important">*</span>
                                                                </span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="util_w-100">
                                                                <x-core::textarea class="textarea-input"
                                                                    label="Rencana Perbaikan"
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
                                                <tr class="selected_section">
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
                                                <tr class="selected_section">
                                                    <td colspan="100">
                                                        <div class="section-inline-form">
                                                            <div class="section-inline-form__label">
                                                                <span>
                                                                    <b>Jenis Temuan</b><span class="important">*</span>
                                                                </span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="util_w-100">
                                                                <div class="form-control">
                                                                    <div class="form-control__group">
                                                                        <x-core::controls.select label="Jenis temuan"
                                                                            purpose="form" :options="$findingTypeOptions"
                                                                            :value="$record['jenis_temuan']" :selected="$record['jenis_temuan']"
                                                                            wire:model="record.jenis_temuan" />
                                                                    </div>
                                                                </div>
                                                                <p class="error-validation">
                                                                    {{ $errors->first('record.jenis_temuan') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="selected_section bottom">
                                                    <td colspan="100">
                                                        <div class="section-inline-form textarea">
                                                            <div class="section-inline-form__label">
                                                                <span>
                                                                    <b>Akar Masalah</b>
                                                                    {{-- <span class="important">*</span> --}}
                                                                </span>
                                                                <span>:</span>
                                                            </div>
                                                            <div class="util_w-100">
                                                                <x-core::textarea class="textarea-input"
                                                                    label="Rencana Perbaikan"
                                                                    value="{{ $record['akar_masalah'] ?? null }}"
                                                                    wire:model="record.akar_masalah" />
                                                                <p class="error-validation">
                                                                    {{ $errors->first('record.akar_masalah') }}
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

    <x-core::modal title="{{ $isFinalized ? 'Batalkan Finalisasi Data' : 'Finalisasi Data' }}" variant="primary"
        id="modal-confirmation-finalize">
        <x-core::modal.body>
            @if ($isFinalized)
                Apakah Anda yakin ingin membatalkan finalisasi data temuan?
            @else
                Mohon diperhatikan bahwa setelah Anda melakukan finalisasi, perubahan ini tidak dapat dibatalkan.
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

    @push('scripts')
        <script>
            document.getElementById('search-input').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        </script>
    @endpush
</x-core::form>
