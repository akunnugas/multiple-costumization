@php
    use Illuminate\Support\Arr;
    use Modules\SPMI\Models\AkreditasiPeringkat;

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $submenu ??= [];
    $dataDetail = $data[0]['items'] ?? [];
    $information = [];

    foreach ($dataDetail as $key => $value) {
        $information[$value['field']] = $value['text'] ?? null;
    }

    $isAkreditasiSyaratAchieved = $information['apakah_syarat_terakreditasi_terpenuhi'];
    $kodeJenjang = $information['kode_jenjang'];
    $information = Arr::except($information, ['apakah_syarat_terakreditasi_terpenuhi', 'kode_jenjang']);

    $butirTidakTerpenuhi = $information['butir_tidak_terpenuhi'] ?? null;
    unset($information['butir_tidak_terpenuhi']);

    $hasilAkhirAudit = Modules\SPMI\Models\HasilAkhirAudit::select('id_penilaian_audit')
        ->where('id', request()->route('hasil_akhir_audit'))
        ->first();
    $penilaianAudit = Modules\SPMI\Models\PenilaianAudit::findOrFail($hasilAkhirAudit->id_penilaian_audit);
    $isHasButirSPME = Modules\SPMI\Models\PenilaianMatriks::where(
        'id_penilaian_panduan',
        $penilaianAudit->id_penilaian_panduan,
    )
        ->where('kategori_penilaian', Modules\SPMI\Models\PenilaianMatriks::CATEGORY_INDICATOR)
        ->where('butir_indikator_spme', true)
        ->exists();
    if (!$isHasButirSPME) {
        unset($information['id_akreditasi_peringkat']);
        unset($information['nilai_iku']);
    }
@endphp

<x-core::layouts.outer :$menu :$title :$subtitle>
    @push('head')
        @vite('Modules/SPMI/Resources/assets/sass/hasil-akhir-audit/create.scss')
        @vite('resources/scss/layouts/_detail.scss')
    @endpush

    <x-core::form id="form_list">
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
                            <a class="btn btn_outline btn_xs" href="{{ Page::indexURL() }}">
                                Kembali ke List
                            </a>
                        </div>

                        <div class="button-group__mobile">
                            <a href="{{ Page::indexURL() }}" class="btn btn_outline btn_icon btn_xs">
                                <span class="icon icon-arrow-left-solid"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card__body util_flex-center">
                    <x-core::layouts.html.alert />
                    <div class="card card_details-default">
                        <div class="card__header" @style(['border: none !important' => true])>
                            <div class="legend">
                                <div class="grid util_grid_row_gap-4">
                                    @foreach ($information as $key => $value)
                                        @php
                                            $label = Page::defineLabelByField($key);
                                            $isMultiLine = Str::contains($value, '<br>');
                                            $lines = explode('<br>', $value);
                                        @endphp
                                        <div class="col-6">
                                            <div class="legend__section" wire:ignore.self
                                                @if ($isMultiLine) style="align-items: flex-start !important;" @endif>
                                                <div class="legend__section_left" wire:ignore>
                                                    <h3>{{ $label }}</h3>
                                                </div>
                                                <span>:</span>
                                                <div class="legend__section_right" wire:ignore.self>
                                                    @if ($isMultiLine && count($lines) > 2)
                                                        <div class="js-expand-container">
                                                            <p>
                                                                {!! implode('<br>', array_slice($lines, 0, 1)) !!}

                                                                <span class="js-expand-content" style="display: none;">
                                                                    <br>{!! implode('<br>', array_slice($lines, 1)) !!}
                                                                </span>
                                                            </p>
                                                            <a href="#" class="js-expand-toggle"
                                                                style="color: #0f6af5;">
                                                                <span class="js-expand-text">Lihat Selengkapnya</span>
                                                            </a>
                                                        </div>
                                                    @else
                                                        <p>{!! $value !!}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const toggleButtons = document.querySelectorAll('.js-expand-toggle');

                                toggleButtons.forEach(button => {
                                    button.addEventListener('click', function(event) {
                                        event.preventDefault();

                                        const container = this.closest('.js-expand-container');
                                        if (!container) return;

                                        const content = container.querySelector('.js-expand-content');
                                        const textElement = container.querySelector('.js-expand-text');

                                        const isHidden = content.style.display === 'none';

                                        if (isHidden) {
                                            content.style.display = 'inline';
                                            textElement.textContent = 'Lihat Sedikit';
                                        } else {
                                            content.style.display = 'none';
                                            textElement.textContent = 'Lihat Selengkapnya';
                                        }
                                    });
                                });
                            });
                        </script>
                        <div class="card__body section-content">
                            <div class="grid col-gap">
                                <div class="@if ($isHasButirSPME) col-6 @else col-12 @endif">
                                    <div class="section">
                                        <div class="section__header">
                                            <h3>Spider Chart AMI</h3>
                                        </div>
                                        <div class="section__body">
                                            <div class="grid">
                                                <div class="col-12 util_d-flex util_flex-center">
                                                    <div class="chart-box">
                                                        <canvas id="criteria-chart-ami"></canvas>
                                                    </div>
                                                </div>
                                                <div class="col-12 util_d-flex util_flex-center gap-16">
                                                    <div class="util_d-flex util_flex-center util_flex-middle gap-8">
                                                        <span class="dot dot-yellow util_h-fit-content"
                                                            style="padding: 8px"></span>
                                                        <p style="font-size: 12.6px">Target AMI</p>
                                                    </div>
                                                    <div class="util_d-flex util_flex-center util_flex-middle gap-8">
                                                        <span class="dot dot-light-blue util_h-fit-content"
                                                            style="padding: 8px"></span>
                                                        <p style="font-size: 12.6px">Realisasi AMI</p>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="chart-desc">
                                                        @php
                                                            $kategoriBelumTercapai =
                                                                $criteriaChart['ami']['data'][
                                                                    'kategori_belum_tercapai'
                                                                ];
                                                            $strkategoriBelumTercapai = implode(
                                                                ', ',
                                                                $kategoriBelumTercapai,
                                                            );
                                                        @endphp
                                                        <p><b>Keterangan</b></p>
                                                        <p>
                                                            @if (!empty($kategoriBelumTercapai))
                                                                Terdapat kriteria yang belum tercapai yaitu
                                                                <b>{{ $strkategoriBelumTercapai }}</b>
                                                            @else
                                                                Semua kriteria sudah mencapai target. Pertahankan!!
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($isHasButirSPME)
                                    <div class="col-6">
                                        <div class="section">
                                            <div class="section__header">
                                                <h3>Spider Chart SPME</h3>
                                            </div>
                                            <div class="section__body">
                                                <div class="grid">
                                                    <div class="col-12 util_d-flex util_flex-center">
                                                        <div class="chart-box">
                                                            <canvas id="criteria-chart-spme"></canvas>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 util_d-flex util_flex-center gap-16">
                                                        <div
                                                            class="util_d-flex util_flex-center util_flex-middle gap-8">
                                                            <span class="dot dot-yellow util_h-fit-content"
                                                                style="padding: 8px"></span>
                                                            <p style="font-size: 12.6px">Target SPME</p>
                                                        </div>
                                                        <div
                                                            class="util_d-flex util_flex-center util_flex-middle gap-8">
                                                            <span class="dot dot-light-blue util_h-fit-content"
                                                                style="padding: 8px"></span>
                                                            <p style="font-size: 12.6px">Realisasi SPME</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="chart-desc">
                                                            <p><b>Keterangan</b></p>
                                                            <p>
                                                                @if (isset($information['id_akreditasi_peringkat']))
                                                                    Target capaian SPME menggunakan skor maksimum Akreditasi
                                                                    yaitu skor peringkat unggul
                                                                @else
                                                                    Target capaian SPME untuk status terakreditasi minimal adalah 80%
                                                                @endif
                                                            </p>

                                                            @php
                                                                // Check if all IKU items are mapped
                                                                $allIkuMapped = true;
                                                                $penilaianMatriks = Modules\SPMI\Models\PenilaianMatriks::where(
                                                                    'id_penilaian_panduan',
                                                                    $penilaianAudit->id_penilaian_panduan,
                                                                )
                                                                    ->where('kategori_penilaian', Modules\SPMI\Models\PenilaianMatriks::CATEGORY_INDICATOR)
                                                                    ->where('butir_indikator_spme', true)
                                                                    ->get();

                                                                $mappingMatriks = Modules\SPMI\Models\MappingPenilaianMatriks::where('id_audit_periode', $penilaianAudit->id_audit_periode)
                                                                    ->where('id_unit', $penilaianAudit->id_unit)
                                                                    ->pluck('id_penilaian_matriks')
                                                                    ->toArray();

                                                                foreach ($penilaianMatriks as $matriks) {
                                                                    if (!in_array($matriks->id, $mappingMatriks)) {
                                                                        $allIkuMapped = false;
                                                                        break;
                                                                    }
                                                                }
                                                            @endphp

                                                            @if (!$allIkuMapped)
                                                                @php
                                                                    $alertIkuWarning = [
                                                                        'title' => 'Informasi',
                                                                        'dismissible' => false,
                                                                        'type' => 'helper',
                                                                        'isHtml' => false,
                                                                        'message' => 'Skor simulasi ini bersifat parsial karena sebagian butir IKU IAPS 5.1 tidak dimapping.',
                                                                    ];
                                                                @endphp
                                                                <br>
                                                                <x-core::layouts.html.alert :data="$alertIkuWarning" class="util_mb-20" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="section">
                                <div class="section__header">
                                    <h3>Hasil Akhir</h3>
                                </div>
                                <div class="section__body grid">
                                    <div class="col-12">
                                        <table class="table-data">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" width="10">No</th>
                                                    <th rowspan="2">Kriteria</th>
                                                    <th colspan="2" style="text-align: center">Hasil Akhir</th>
                                                    <th colspan="2" style="text-align: center">Persentase Hasil</th>
                                                </tr>
                                                <tr>
                                                    <th rowspan="2" width="20">Target Capaian</th>
                                                    <th rowspan="2" width="20">Skor Capaian</th>
                                                    <th rowspan="2" width="20">Target Capaian</th>
                                                    <th rowspan="2" width="20">Skor Capaian</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $num = 1;
                                                @endphp
                                                @foreach ($elementScoreData as $key => $elementScore)
                                                    <tr>
                                                        @if ($elementScore['info_level'] == 0)
                                                            <td colspan="2">
                                                                <div>
                                                                    @if ($elementScore['nomor_penilaian'] === 'total')
                                                                        <b>{!! $elementScore['pertanyaan_penilaian'] !!}</b>
                                                                    @else
                                                                        {!! $elementScore['pertanyaan_penilaian'] !!}
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        @else
                                                            <td>{{ $num }}</td>
                                                            <td>{!! $elementScore['pertanyaan_penilaian'] !!}</td>
                                                            @php
                                                                $num++;
                                                            @endphp
                                                        @endif
                                                        <td class="align-right">
                                                            {{ $elementScore['bobot_target'] ?? null }}
                                                        </td>
                                                        <td class="align-right">
                                                            {{ $elementScore['nilai_akhir'] ?? null }}
                                                        </td>
                                                        <td class="align-right">
                                                            {{ isset($elementScore['persentase_bobot_target']) ? $elementScore['persentase_bobot_target'] . '%' : null }}
                                                        </td>
                                                        <td class="align-right">
                                                            {{ isset($elementScore['persentase_nilai_akhir']) ? $elementScore['persentase_nilai_akhir'] . '%' : null }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-12" style="line-height: 1.4rem; margin-top: 8px">
                                        <p><b>Keterangan</b></p>
                                        <p>1. Skor penilaian didapat dari nilai yang diberikan auditor</p>
                                        <p>2. Skor akhir adalah hasil dari Bobot * Skor penilaian</p>
                                        <p>3. Persentase target capaian adalah (Target Capaian / Skor Maksimal) * 100
                                        </p>
                                        <p>3. Persentase hasil adalah (Skor Capaian / Skor Maksimal) * 100</p>
                                    </div>
                                </div>
                            </div>
                            <div class="section">
                                <div class="section__header">
                                    <h3>Kesimpulan</h3>
                                </div>
                                <div class="section__body">
                                    <div class="alert alert alert_helper">
                                        <div class="alert__content">
                                            <p>
                                                @if ($isHasButirSPME)
                                                    @if (!$isAkreditasiSyaratAchieved)
                                                        @if (isset($information['id_akreditasi_peringkat']))
                                                            Skor hasil AMI adalah
                                                            <b>{{ $information['persentase_nilai_akhir'] }}</b> dengan
                                                            peringkat AMI adalah
                                                            <b>{{ $information['nama_spmi_peringkat'] }}</b>. Berdasarkan
                                                            hasil simulasi penilaian SPME, skor yang diperoleh adalah
                                                            <b>{{ $information['nilai_iku'] }}</b>, namun tidak memenuhi
                                                            syarat terakreditasi/kadaluarsa.
                                                            <br>
                                                            <br><b>Beberapa indikator yang tidak terpenuhi</b> tercantum di bawah
                                                            ini: <br>
                                                            {!! $butirTidakTerpenuhi !!}
                                                        @else
                                                            Skor hasil AMI adalah
                                                            <b>{{ $information['persentase_nilai_akhir'] }}</b> dengan
                                                            status akreditasi <b>{{ $information['nama_akreditasi_status'] }}</b>. Berdasarkan
                                                            hasil simulasi penilaian SPME, skor yang diperoleh adalah
                                                            <b>{{ $information['nilai_iku'] }}</b>.
                                                        @endif
                                                    @else
                                                        @if (isset($information['id_akreditasi_peringkat']))
                                                            Selamat, hasil AMI menunjukkan skor
                                                            <b>{{ $information['persentase_nilai_akhir'] }}</b> dengan
                                                            peringkat <b>{{ $information['nama_spmi_peringkat'] }}</b>,
                                                            serta skor SPME <b>{{ $information['nilai_iku'] }}</b> dengan
                                                            status <b>{{ $information['id_akreditasi_peringkat'] }}</b>
                                                        @else
                                                            Selamat, hasil AMI menunjukkan skor
                                                            <b>{{ $information['persentase_nilai_akhir'] }}</b> dengan
                                                            status akreditasi <b>{{ $information['nama_akreditasi_status'] }}</b>,
                                                            serta skor SPME <b>{{ $information['nilai_iku'] }}</b>
                                                        @endif
                                                    @endif
                                                @else
                                                    Skor hasil AMI adalah
                                                    <b>{{ $information['persentase_nilai_akhir'] }}</b> dengan
                                                    peringkat AMI adalah
                                                    <b>{{ $information['nama_spmi_peringkat'] }}</b>.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-core::form>

    @push('scripts')
        <script type="text/javascript" src="{{ Page::quantumAsset('js/vendors/chart.js-4.3.0/dist/chart.umd.js') }}"></script>
        <script type="text/javascript"
            src="{{ Page::quantumAsset('js/vendors/chartjs-plugin-datalabels-2.2.0/dist/chartjs-plugin-datalabels.min.js') }}">
        </script>

        <script>
            const chartScoreAMI = @json($criteriaChart['ami']);
            const chartScoreSPME = @json($criteriaChart['spme']);

            buildCriteriaChart('criteria-chart-ami', "Realisasi AMI", "Target AMI", chartScoreAMI?.category, chartScoreAMI?.data
                ?.ketercapaian,
                chartScoreAMI
                ?.data?.bobot_target);

            buildCriteriaChart('criteria-chart-spme', "Realisasi SPME", "Target SPME", chartScoreSPME?.category, chartScoreSPME
                ?.data
                ?.ketercapaian,
                chartScoreSPME
                ?.data?.bobot_target);

            function buildCriteriaChart(id, labelCapaian, labelTarget, category = [], chartDataKetercapaian = [],
                chartDataBobotTarget = []) {
                const data = {
                    labels: category,
                    datasets: [{
                        label: labelCapaian,
                        data: chartDataKetercapaian,
                        fill: true,
                        backgroundColor: 'rgba(93, 136, 246, 0.3)',
                        borderColor: '#a4c0f9',
                        pointBackgroundColor: '#5d88f6',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#5d88f6'
                    }, {
                        label: labelTarget,
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
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        animation: {
                            duration: 0
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

                buildChart(id, config);
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
</x-core::layouts.outer>
