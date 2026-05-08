@props([
    'data' => [],
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'resourceId' => null,
    'resourceTitle' => null,
])
@php
    // default title
    $actionLabelContext = empty($resourceId) ? 'Tambah' : 'Edit';
    if (empty($title)) {
        $title = $actionLabelContext . ' ' . $resourceTitle;
    }

    // parameter form
    if (empty($resourceId)) {
        $method = 'POST';
        $action = Page::indexURL();
    } else {
        $method = 'PUT';
        $action = Page::detailURL($resourceId);
    }

    // buat table of content jika advanced
    $toc = [];
    if (!empty(current($data)['items'])) {
        foreach ($data as $i => $section) {
            if (empty($section['id'])) {
                $section['id'] = Str::kebab($section['title']);
            }

            $toc[$section['id']] = $section['title'];
            $data[$i] = $section;
        }
    }
@endphp
<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    <x-core::form :$method :$action>
        <div class="form-nav">
            <div class="form-nav__left">
                <a href="{{ $action }}" class="btn btn_outline">
                    <span class="icon icon-arrow-left-mini"></span>
                    <span class="btn__text">Kembali</span>
                </a>
            </div>
            <div class="form-nav__middle">
                <ul class="form-nav__breadcrumb">
                    <li class="form-nav__breadcrumb-item active">
                        <a href="#" class="form-nav__breadcrumb-btn">{{ $title }}</a>
                    </li>
                </ul>
            </div>
            <div class="form-nav__right">
                <div class="form-nav__wrapper">
                    <div class="form-nav__button-wrapper">
                        <button type="submit" class="btn btn_primary" id="submit_btn">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @if (!empty($resourceId) && !$isEdit)
            <script>
                setTimeout(() => {
                    document.getElementById('submit_btn').disabled = true;
                }, 100);
            </script>
        @endif
        @if ($toc)
            <x-core::layouts.create.toc :data="$toc" />
        @endif
        <x-core::layouts.main.container :$menu :$title :$subtitle>
            @if (!empty($resourceId) && !$isEdit)
                <div class="alert alert_warning">
                    <div class="alert__content">
                        <p>
                            Data Sumber Pendanaan tidak dapat diubah karena sudah digunakan pada pembuatan <b>Klaster
                                Pendanaan</b>.
                        </p>
                    </div>
                </div>
            @endif

            <x-core::layouts.html.alert style="margin-bottom:1rem" />

            <div class="grid">
                <div class="col-12">
                    <div {{ $attributes->merge(['class' => 'card card_form', 'id' => Str::kebab($title)]) }}>
                        @if (!empty($title))
                            <div class="card__header">
                                <div class="form-header">
                                    <div class="form-header__wrapper">
                                        <div class="form-header__avatar">
                                            <span class="icon icon-{{ $icon ?? 'cube' }}-mini"></span>
                                        </div>
                                        <div class="form-header__information">
                                            <h3 class="form-header__title">{{ $title }}</h3>
                                            <p class="form-header__subtitle">{{ $subtitle }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="card__body">
                            <div class="grid cols-1">
                                @foreach ($data as $item)
                                    @php
                                        $item['name'] ??= $item['field'];
                                        unset($item['field']);

                                        if (!empty($resourceId) && !$isEdit) {
                                            $item['disabled'] = true;
                                        }

                                        $attributes = Page::buildAttributes($item);
                                    @endphp
                                    <x-core::controls.form {{ $attributes }} />
                                @endforeach

                                {{-- Custom Form utk Tahapan Kegiatan --}}
                                @php
                                    $alertAgenda = [
                                        'type' => 'helper',
                                        'message' =>
                                            'Jadwal Tahapan Kegiatan yang anda aktifkan, akan digunakan pada setiap klaster dari sumber pendanaan ini.',
                                    ];
                                @endphp
                                <x-core::layouts.html.alert :data="$alertAgenda" class="util_mb-20" />

                                <div class="form-control">
                                    <label for="form-control-agenda-kegiatan" class="form-control__label"
                                        style="padding-bottom: 0;">
                                        Pilih Tahapan Kegiatan pada sumber pendanaan ini
                                    </label>
                                </div>
                                <x-core::table>
                                    <div class="box-table">
                                        <div class="box-table__content">
                                            <div class="table-max">
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th class="cell-check cell-center">No</th>
                                                            <th>Tahapan Kegiatan</th>
                                                            <th class="cell-action cell-center">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($sourceAgendaMappings as $value)
                                                            @php
                                                                // check old
                                                                $oldAgendaChecked = old('optional_agenda_ids', []);
                                                                $oldAgendaChecked = in_array(
                                                                    $value->id,
                                                                    $oldAgendaChecked,
                                                                );
                                                                $checked =
                                                                    $value->apakah_wajib ||
                                                                    $value->is_checked ||
                                                                    $oldAgendaChecked;

                                                                $isWajib = $value->apakah_wajib;
                                                                $disabled =
                                                                    $value->apakah_wajib ||
                                                                    (!empty($resourceId) && !$isEdit);
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    {{ $loop->iteration }}
                                                                </td>
                                                                <td>
                                                                    {{ $value->nama_agenda }} @if ($isWajib)
                                                                        <span style="color: red;"><small>(Wajib)</small></span>
                                                                    @endif

                                                                    @if (in_array($value->kode_agenda, [Modules\Litabmas\Models\AgendaKegiatan::STEP_PENILAIAN_HASIL_PRESENTASI, Modules\Litabmas\Models\AgendaKegiatan::STEP_PENILAIAN_LAPORAN_ANTARA]))
                                                                        <br> <small style="color: #697586">*Berkaitan dengan Tahapan Peninjauan Proposal</small>
                                                                    @endif

                                                                    @if (in_array($value->kode_agenda, [Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMUMAN_NOMINASI]))
                                                                        <br> <small style="color: #697586">*Berkaitan dengan Tahapan Penentuan Nominasi</small>
                                                                    @endif
                                                                </td>
                                                                <td class="cell-check cell-center">
                                                                    <x-core::checkbox.control class="check-item"
                                                                        data-checkbox="{{ $value->kode_agenda }}"
                                                                        name="optional_agenda_ids[]"
                                                                        value="{{ $value->id }}"
                                                                        checked="{{ $checked }}"
                                                                        disabled="{{ $disabled }}" />
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        @php
                                                            $requiredAgendaMapping =
                                                                Modules\Litabmas\Models\AgendaKegiatan::REQUIRED_AGENDA_MAPPING;
                                                            $validationCheckbox =
                                                                Modules\Litabmas\Models\AgendaKegiatan::VALIDATION_REQUIRED_MAPPING;
                                                        @endphp

                                                        <script>
                                                            document.addEventListener('DOMContentLoaded', () => {
                                                                const requiredAgendaMapping = @json($requiredAgendaMapping);
                                                                const validationCheckbox = @json($validationCheckbox);

                                                                document.querySelectorAll('.check-item').forEach((el) => {
                                                                    el.addEventListener('change', handleCheckboxChange);
                                                                });

                                                                function handleCheckboxChange(e) {
                                                                    const dataCheckbox = e.target.getAttribute('data-checkbox');
                                                                    const isChecked = e.target.checked;

                                                                     // dynamic
                                                                     if (isChecked) {
                                                                        checkRequiredCheckboxes(dataCheckbox);
                                                                    } else {
                                                                        uncheckValidationCheckboxes(dataCheckbox);
                                                                    }
                                                                }

                                                                function checkRequiredCheckboxes(dataCheckbox) {
                                                                    if (requiredAgendaMapping[dataCheckbox] !== undefined) {
                                                                        requiredAgendaMapping[dataCheckbox].forEach(required => {
                                                                            let requiredCheckbox = document.querySelector(
                                                                                `.check-item[data-checkbox="${required}"]`);
                                                                            if (requiredCheckbox && !requiredCheckbox.checked) {
                                                                                requiredCheckbox.checked = true;
                                                                                requiredCheckbox.dispatchEvent(new Event(
                                                                                'change')); // Trigger event
                                                                            }
                                                                        });
                                                                    }
                                                                }

                                                                function uncheckValidationCheckboxes(dataCheckbox) {
                                                                    if (validationCheckbox[dataCheckbox] !== undefined) {
                                                                        validationCheckbox[dataCheckbox].forEach(validation => {
                                                                            let validationCheckboxEl = document.querySelector(
                                                                                `.check-item[data-checkbox="${validation}"]`);
                                                                            if (validationCheckboxEl && validationCheckboxEl.checked) {
                                                                                validationCheckboxEl.checked = false;
                                                                                validationCheckboxEl.dispatchEvent(new Event(
                                                                                'change')); // Trigger event
                                                                            }
                                                                        });
                                                                    }
                                                                }
                                                            });
                                                        </script>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </x-core::table>
                            </div>
                            <p>*Pengisian tanggal tahapan kegiatan dilakukan pada bagian klaster pendanaan</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-core::layouts.main.container>
    </x-core::form>

    @pushonce('head')
        <style>
            .box-table__content {
                padding: 0 !important;
                border-top: none !important;
            }
        </style>
    @endpushonce
</x-core::layouts.outer>
