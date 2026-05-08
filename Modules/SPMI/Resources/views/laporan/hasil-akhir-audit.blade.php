@php
    use Carbon\Carbon;

    $title = Page::defineLabelByField('main') . ' ' . $data['self']->nama_prodi;
    $auditDate =
        Carbon::parse($data['self']->tanggal_awal_penilaian)->translatedFormat('d F Y') .
        ' s.d. ' .
        Carbon::parse($data['self']->tanggal_akhir_penilaian)->translatedFormat('d F Y');

    use Modules\SPMI\Models\SuratTugasAuditorPegawai;
@endphp

<x-core::layouts.html :$title :isReport="true">
    @push('head')
        @vite('Modules/SPMI/Resources/assets/sass/hasil-akhir-audit/report.scss')
        <style type="text/css" media="print">
            @page {
                size: auto;
                margin: 0mm;
                size: A4 portrait;
            }
        </style>
        <script type="text/javascript" src="{{ Page::quantumAsset('js/vendors/chart.js-4.3.0/dist/chart.umd.js') }}"></script>
        <script type="text/javascript"
            src="{{ Page::quantumAsset('js/vendors/chartjs-plugin-datalabels-2.2.0/dist/chartjs-plugin-datalabels.min.js') }}">
        </script>
    @endpush

    <main>
        <table>
            <thead style="font-family: times new roman;">
                <tr>
                    {{-- <td style="width: 20%;" align="center"><img src="../assets/v1/img/logo.png" width="96"
                                height="99">
                        </td> --}}
                    <th align="center" class="report-header" style="border: none !important">
                        <p class="report-header__text">
                            <span style="font-size: 16px; font-weight: 500">KEMENTRIAN RISET, TEKNOLOGI, DAN PENDIDIKAN
                                TINGGI</span>
                            <br>
                            <b style="font-size: 18px;">{{ strtoupper($data['university']->nama_unit) }}</b>
                            <br>
                            <b style="font-size: 16px;">UNIT PENJAMINAN MUTU</b>
                            {{-- <br>
                            <span style="font-size: 16px; font-weight: 500">Alamat - Kode Pos</span>
                            <br>
                            <span style="font-size: 16px; font-weight: 500">Telp: xxx; Email: yyy</span> --}}
                        </p>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="1000" style="border: none !important">
                        <div class="label-form">
                            <span>Form 1</span>
                        </div>
                        <div class="report-title">
                            <p class="report-title__text">
                                <b style="font-size: 24px;">BERITA ACARA</b>
                                <br>
                                <b style="font-size: 24px;">AMI</b>
                            </p>
                        </div>

                        <div class="section-list">
                            <table class="introduction">
                                <tbody>
                                    <tr>
                                        <td colspan="2"><b>Hari/Tanggal Audit</b></td>
                                        <td class="introduction__value">{{ $auditDate }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Periode AMI</b></td>
                                        <td class="introduction__value">{{ $data['self']->periode_audit }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Nama Kegiatan AMI</b></td>
                                        <td class="introduction__value">{{ $data['self']->nama_jadwal_audit }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Panduan Penilaian</b></td>
                                        <td class="introduction__value">{{ $data['self']->panduan_penilaian ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Fakultas/Departemen</b></td>
                                        <td class="introduction__value">{{ $data['self']->nama_fakultas }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Program Pendidikan</b></td>
                                        <td class="introduction__value">{{ $data['self']->nama_jenjang }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Unit Kerja</b></td>
                                        <td class="introduction__value">{{ $data['self']->nama_prodi }}
                                        </td>
                                    </tr>

                                    {{-- TTD Ketua Prodi --}}
                                    <tr>
                                        <td rowspan="2"><b>Ketua Unit Kerja</b></td>
                                        <td><b>Nama</b></td>
                                        <td class="introduction__value">{{ $data['self']->kaprodi }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Tanda Tangan</b></td>
                                        <td style="height: 8rem"></td>
                                    </tr>

                                    {{-- TTD Ketua Auditor --}}
                                    <tr>
                                        <td rowspan="2"><b>Ketua Auditor</b></td>
                                        <td><b>Nama</b></td>
                                        <td class="introduction__value">{{ $data['self']->ketua_auditor }}</td>
                                    </tr>

                                    <tr>
                                        <td><b>Tanda Tangan</b></td>
                                        <td style="height: 8rem"></td>
                                    </tr>

                                    @php
                                        $data['members'] = $data['members'] ?? [];
                                        $ketuaAuditeeExist = 0;
                                        $memberAuditeeExist = 0;
                                        $memberAuditorExist = 0;
                                    @endphp
                                    <tr>
                                        <td colspan="2"><b>Ketua Auditee</b></td>
                                        <td class="introduction__value">
                                            @foreach ($data['members'] as $index => $member)
                                                @if ($member->posisi == SuratTugasAuditorPegawai::POSITION_AUDITEE)
                                                    @php
                                                        $ketuaAuditeeExist++;
                                                    @endphp
                                                    @if ($ketuaAuditeeExist > 1)
                                                        <br>
                                                    @endif
                                                    {{ $member->gelar_depan }}
                                                    {{ $member->nama }}
                                                    {{ $member->gelar_belakang }}
                                                @endif
                                            @endforeach

                                            @if ($ketuaAuditeeExist == 0)
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Anggota Auditee</b></td>
                                        <td class="introduction__value">
                                            @foreach ($data['members'] as $index => $member)
                                                @if ($member->posisi == SuratTugasAuditorPegawai::POSITION_MEMBER_AUDITEE)
                                                    @php
                                                        $memberAuditeeExist++;
                                                    @endphp
                                                    @if ($memberAuditeeExist > 1)
                                                        <br>
                                                    @endif
                                                    {{ $member->gelar_depan }}
                                                    {{ $member->nama }}
                                                    {{ $member->gelar_belakang }}
                                                @endif
                                            @endforeach

                                            @if ($memberAuditeeExist == 0)
                                                -
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2">
                                            <b>Anggota Auditor</b>
                                        </td>
                                        <td class="introduction__value">
                                            @foreach ($data['members'] as $index => $member)
                                                @if ($member->posisi == SuratTugasAuditorPegawai::POSITION_MEMBER)
                                                    @php
                                                        $memberAuditorExist++;
                                                    @endphp
                                                    @if ($memberAuditorExist > 1)
                                                        <br>
                                                    @endif
                                                    {{ $member->gelar_depan }}
                                                    {{ $member->nama }}
                                                    {{ $member->gelar_belakang }}
                                                @endif
                                            @endforeach

                                            @if ($memberAuditorExist == 0)
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <ol class="section-list__ol">
                                <li class="section-list__ol__li">
                                    <span class="title"><b>TUJUAN AUDIT</b></span>
                                    <div class="italic-blue" style="margin-top: 6px">(Tuliskan Lingkup Audit)</div>
                                </li>
                                <li class="section-list__ol__li">
                                    <span class="title"><b>LINGKUP AUDIT</b></span>
                                    <ol class="desc-list">
                                        @foreach ($data['criteria_data'] as $criteria)
                                            <li>{{ $criteria[0] }}</li>
                                </li>
                                @endforeach
                            </ol>
                            </li>
                            <li class="section-list__ol__li">
                                <span class="title"><b>RINGKASAN HASIL AUDIT</b></span>
                                <ol class="desc-list-2">
                                    <li>
                                        <span><b>Total jumlah indikator:</b></span>
                                        <span class="italic-blue">{{ $data['totals']['total_matrix'] }}
                                            Indikator</span>
                                    </li>
                                    <li>
                                        <span><b>Jumlah Praktik Baik (temuan positif):</b></span>
                                        <span class="italic-blue">{{ $data['totals']['achieved_finding'] }}
                                            Indikator ({{ $data['totals']['achieved_finding_percent'] }})</span>
                                    </li>
                                    <li>
                                        <span>
                                            <b>Total jumlah temuan</b>
                                            <span class="italic-blue">{{ $data['totals']['finding'] }}
                                                Indikator
                                                ({{ $data['totals']['finding_percent'] }})</span>
                                            <b>dengan Rincian:</b>
                                        </span>
                                        <ol class="desc-list-alphabet">
                                            <li>
                                                <span>
                                                    <b>Jumlah Temuan OBS:</b>
                                                    <span
                                                        class="italic-blue">{{ $data['totals']['kts_observation_finding'] }}
                                                        Indikator
                                                        ({{ $data['totals']['kts_observation_percent'] }})</span>
                                                </span>
                                                </span>
                                            </li>
                                            <li>
                                                <span>
                                                    <b>Jumlah temuan Ketidaksesuaian Mayor:</b>
                                                    <span
                                                        class="italic-blue">{{ $data['totals']['kts_major_finding'] }}
                                                        Indikator
                                                        ({{ $data['totals']['kts_major_percent'] }})</span>
                                                </span>
                                            </li>
                                            <li>
                                                <span>
                                                    <b>Jumlah temuan Ketidaksesuaian Minor:</b>
                                                    <span
                                                        class="italic-blue">{{ $data['totals']['kts_minor_finding'] }}
                                                        Indikator
                                                        ({{ $data['totals']['kts_minor_percent'] }})</span>
                                                </span>
                                            </li>
                                            <li>
                                                <span>
                                                    <b>Jumlah indikator yang belum memiliki jenis temuan:</b>
                                                    <span
                                                        class="italic-blue">{{ $data['totals']['undifined_finding'] }}
                                                        Indikator
                                                        ({{ $data['totals']['undifined_finding_percent'] }})</span>
                                                </span>

                                                <div class="util_d-flex util_flex-middle util_flex-column"
                                                    style="margin-top: 32px; text-align:center; margin-bottom: 32px">
                                                    <div class="finding-chart-box">
                                                        <canvas id="finding-chart"></canvas>
                                                    </div>
                                                    <span class="italic-blue" style="margin-top: 24px">
                                                        Gambar 1. Prosentase hasil audit pada indikator dengan
                                                        kategori temuan positif, observe, ketidaksesuaian mayor, dan
                                                        ketidaksesuaian minor.
                                                    </span>
                                                </div>
                                            </li>
                                        </ol>
                                    </li>
                                </ol>
                            </li>
                            <li class="section-list__ol__li">
                                <b>Rekapitulasi skor hasil audit per kriteria</b>
                                <div style="margin-top: 36px">
                                    <div class="col-12 util_d-flex util_flex-center">
                                        <div style="width: 100%">
                                            <canvas id="recap-chart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="chart-desc">
                                            <p><b>Legend</b></p>
                                            @foreach ($data['criteria_data'] as $criteria)
                                                <p>{{ $criteria[0] }}
                                                </p>
                                            @endforeach
                                        </div>
                                        <div class="chart-desc">
                                            <p><b>Keterangan</b></p>
                                            <p>
                                                Skor hasil AMI adalah
                                                <b>{{ $data['self']->persentase_nilai_akhir }}</b> dengan
                                                peringkat AMI adalah
                                                <b>{{ $data['self']->nama_spmi_peringkat }}</b>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="section-list__ol__li">
                                <span class="title"><b>KESIMPULAN AUDIT</b></span>
                                <div class="italic-blue" style="margin-top: 6px">
                                    Skor akhir audit terhadap Program
                                    Studi Sarjana <b>{{ $data['self']->study_program_name }}</b> adalah:
                                </div>
                                <ol class="desc-list">
                                    @if ($data['is_has_butir_spme'])
                                        <li>
                                            <span>Skor akhir audit terhadap capaian IKU</span>
                                            <div class="util_d-flex util_flex-start">
                                                <div class="italic-blue" style="margin-right: 8px">Skor: </div>
                                                <div class="italic-blue"><b>{{ $data['self']['nilai_iku'] }}</b></div>
                                            </div>
                                            <div class="util_d-flex util_flex-start">
                                                <div class="italic-blue" style="margin-right: 8px">Status: </div>
                                                <div class="italic-blue">
                                                    <b>{{ !$data['self']['apakah_syarat_terakreditasi_terpenuhi'] ? 'Tidak Terakreditasi' : 'Terakreditasi' }}</b>
                                                </div>
                                            </div>
                                            <div class="util_d-flex util_flex-start">
                                                <div class="italic-blue" style="margin-right: 8px">Peringkat: </div>
                                                <div class="italic-blue">
                                                    <b>{{ $data['self']['nama_peringkat_akreditasi'] }}</b>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <span>Skor akhir audit terhadap keseluruhan capaian IKU dan IKT</span>
                                            <div class="util_d-flex util_flex-start">
                                                <div class="italic-blue" style="margin-right: 8px">Skor: </div>
                                                <div class="italic-blue"><b>{{ $data['self']['nilai_akhir'] }}</b>
                                                </div>
                                            </div>
                                            <div class="util_d-flex util_flex-start">
                                                <div class="italic-blue" style="margin-right: 8px">Peringkat: </div>
                                                <div class="italic-blue">
                                                    <b>{{ $data['self']['nama_spmi_peringkat'] }}</b>
                                                </div>
                                            </div>
                                        </li>
                                    @else
                                        <li>
                                            <span>Skor akhir audit terhadap capaian IKT</span>
                                            <div class="util_d-flex util_flex-start">
                                                <div class="italic-blue" style="margin-right: 8px">Skor: </div>
                                                <div class="italic-blue"><b>{{ $data['self']['nilai_ikt'] }}</b></div>
                                            </div>
                                            <div class="util_d-flex util_flex-start">
                                                <div class="italic-blue" style="margin-right: 8px">Peringkat: </div>
                                                <div class="italic-blue">
                                                    <b>{{ $data['self']['nama_spmi_peringkat'] }}</b>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                </ol>
                            </li>
                            <li class="section-list__ol__li">
                                <span class="title"><b>LAMPIRAN AUDIT</b></span>
                                <div class="italic-blue" style="margin-top: 6px">(Silakan Tuliskan Lampiran Audit)
                                </div>
                                <ol class="desc-list-3">
                                    <li>
                                        <span>Daftar Pertanyaan (Checklist) dan Hasil Observasi </span>
                                    </li>
                                    <li>
                                        <span>Daftar Praktik Baik (Temuan Positif)</span>
                                    </li>
                                    <li>
                                        <span>Daftar Praktik Buruk (Temuan Negatif)</span>
                                    </li>
                                    <li>
                                        <span>Daftar Permintaan Tindakan Koreksi (PTK)</span>
                                    </li>
                                    <li>
                                        <span>Daftar Hadir Audit</span>
                                    </li>
                                </ol>
                            </li>
                            </ol>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="spacer"></td>
                </tr>
            </tfoot>
        </table>

        <div class="pagebreak"></div>
    </main>

    @push('scripts')
        <script>
            const chartScoreAMI = @json($data['chart_ami']);

            buildFindingChart();

            buildCriteriaChart('recap-chart', "Realisasi AMI", "Target AMI", chartScoreAMI?.category, chartScoreAMI?.data
                ?.ketercapaian,
                chartScoreAMI
                ?.data?.bobot_target);

            function buildFindingChart() {
                const category = {!! json_encode($data['chart_finding']['category']) !!};
                const chartData = {!! json_encode($data['chart_finding']['data']) !!};
                const data = {
                    labels: category,
                    datasets: [{
                        label: 'Hasil Temuan',
                        data: chartData,
                        backgroundColor: [
                            '#6280b8',
                            '#ab5751',
                            '#a2b967',
                            '#7b669e'
                        ],
                        hoverOffset: 4
                    }]
                };

                const config = {
                    type: 'pie',
                    data: data,
                    options: {
                        animation: {
                            duration: 0
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                };

                buildChart('finding-chart', config);
            }

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
                new Chart(ctx, config);
            }
        </script>
    @endpush
</x-core::layouts.html>
