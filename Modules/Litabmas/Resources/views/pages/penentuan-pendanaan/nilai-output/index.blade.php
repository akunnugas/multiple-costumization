@php use Modules\Core\Helpers\Date; @endphp
@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'action' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $sudahMasaPenilaian = !empty($infoPengumpulanOutput['sudah_masuk_masa']);
    if (!$sudahMasaPenilaian) {
        $tanggal = Date::formatDateRange($infoPengumpulanOutput['waktu_mulai'], $infoPengumpulanOutput['waktu_selesai'], isoFormatMonth: 'MMMM');
        $staticAlertKesimpulan = [
            'message' => 'Anda tidak dapat memberikan kesimpulan karena bukan dalam waktu nya.
                Anda dapat memberikan kesimpulan pada tanggal ' . $tanggal . '.',
            'type' => 'helper',
            'dismissible' => false
        ];
    }

    $urlEditKesimpulan = \Modules\Core\Helpers\Page::buildURL(['edit' => true]) . '#kesimpulan';
    $canUpdate = true;
    if (!$sudahMasaPenilaian) {
        $canUpdate = false;
        $urlEditKesimpulan = '#';
    }
@endphp

<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu"/>
    </x-slot:sidebar>

    <x-core::layouts.html.alert/>

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$primaryData"/>
        </div>
    </div>

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1" title="Informasi Reviewer">
        <div class="col-12">
            <div class="util_d-flex util_flex-column">
                @foreach($dataOutputBersama['biodata_reviewer'] as $reviewerKe => $reviewer)
                    <ul>
                            <li class="util_mb-8 util_ml-16">{{ $reviewer['nama'] }}</li>
                    </ul>
                @endforeach
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1"
                                     title="Hasil Penilaian Presentasi Output">
        <div class="col-12">
            <div class="grid cols-1">
                @if(!empty($dataPaginateFeedbackReviewer->items))
                    <x-core::table>
                        <x-core::table.data :header="$headerFeedbackReviewer" :data="$dataPaginateFeedbackReviewer->items"
                                            :showNumber="true"
                                            :paginateInfo="$dataPaginateFeedbackReviewer" :resourceTitle="$title"/>
                    </x-core::table>
                @else
                    <div class="form-control">
                        <div class="form-control__text">
                            -- Belum ada penilaian presentasi output --
                        </div>
                    </div>
                @endif
                <div class="form-control">
                    <label class="form-control__label">Komentar Umum</label>
                    <div class="form-control__text">
                        @if(!empty($dataKomentarUmum))
                            @foreach ($dataKomentarUmum as $komentar)
                                {{ $komentar }}<br>
                            @endforeach
                        @else
                            -- Belum ada komentar umum --
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1"
                                     title="Hasil Penilaian Akhir Output (Bersama)">
        <div class="col-12">
            <div class="grid cols-1">
                @if(!$dataPenilaianOutputBersama->isEmpty())
                    <x-core::table>
                        <x-core::table.data :header="$headerPenilaianOutputBersamaFields" :data="$dataPenilaianOutputBersama"
                                            :showNumber="true"
                                            :paginateInfo="$dataPaginateFeedbackReviewer" :resourceTitle="$title" />
                    </x-core::table>
                @else
                    <div class="form-control">
                        <div class="form-control__text">
                            -- Belum ada penilaian akhir output bersama --
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    @if(!empty($staticAlertKesimpulan))
        <x-core::layouts.html.alert :data="$staticAlertKesimpulan"/>
    @endif

    <x-core::form method="PUT" action="{{ route('litabmas.penentuan-pendanaan.nilai-output.update', $resourceId) }}"
                  id="form-action-kesimpulan">
        <x-litabmas::layouts.detail.card customClassBody="util_d-flex util_flex-column util_gap-1"
                                         title="Kesimpulan">
            <x-slot:action>
                <div class="util_d-flex">
                    @if(!$isEdit)
                        <x-core::button href="{{ $urlEditKesimpulan }}" leading-icon="pencil-solid" size="sm"
                                        :disabled="!$canUpdate">
                            Edit Data
                        </x-core::button>
                    @else
                        <x-core::button href="{{ route('litabmas.penentuan-pendanaan.nilai-output.index', $resourceId) }}"
                                        size="sm" variant="outline" class="util_mr-8">
                            Batalkan
                        </x-core::button>
                        <x-core::button type="{{ $canUpdate ? 'submit' : 'button' }}" size="sm"
                                        form="form-action-kesimpulan" :disabled="!$canUpdate">
                            Simpan
                        </x-core::button>
                    @endif
                </div>
            </x-slot:action>

            <div class="col-12" id="kesimpulan">
                <div class="grid cols-1">
                    @if($isEdit)
                        @php
                            $attributes = Page::buildAttributes($fieldKesimpulanOutputBersama);
                        @endphp
                        <div class="grid cols-1 cols-sm-2">
                            <x-core::controls.form {{ $attributes }} />
                        </div>
                    @else
                        <div class="form-control">
                            <div class="form-control__text">
                                @if(!empty($fieldKesimpulanOutputBersama['value']))
                                    {{ $fieldKesimpulanOutputBersama['value'] }}
                                @else
                                    -- Masukkan kesimpulan berdasarkan keseluruhan hasil penelitian yang didapatkan serta hasil rekomendasi dari reviewer--
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </x-litabmas::layouts.detail.card>
    </x-core::form>

    @pushonce('head')
        <style>
            .box-table__content {
                border-top: none;
                padding: 0;
            }
            .form-control .form-control__label {
                color: #364152 !important;
            }
            .form-control__text, .nama-reviewer {
                color: #697586;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
