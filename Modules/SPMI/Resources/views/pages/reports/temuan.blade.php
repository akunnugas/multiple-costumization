@php
    use Carbon\Carbon;
    use Modules\SPMI\Models\AuditTemuan;
    use Modules\SPMI\Models\SuratTugasAuditorPegawai;

    $data['self'] = $data['self'] ?? null;

    if (!empty($data['self']->nama_prodi)) {
        $title = 'Laporan Hasil Temuan - ' . $data['self']->nama_prodi;
    } else {
        $title = 'Laporan Hasil Temuan';
    }
@endphp

<x-core::layouts.reports.show :title="$title">
    @push('head')
        @vite('Modules/SPMI/Resources/assets/sass/reports/default-laporan.scss')
        <style type="text/css" media="print">
            @page {
                size: auto;
                margin: 0mm;
                size: A4 portrait;
            }
        </style>
    @endpush

    <main class="main-report">
        <table>
            @if ($isUsingKop)
                <thead style="font-family: times new roman;">
                    <tr>
                        <th align="center" class="report-header" style="border: none !important">
                            <p class="report-header__text">
                                <span style="font-size: 16px; font-weight: 500">KEMENTRIAN RISET, TEKNOLOGI, DAN
                                    PENDIDIKAN
                                    TINGGI</span>
                                <br>
                                <b style="font-size: 18px;">{{ strtoupper($data['university']?->nama_unit) }}</b>
                                <br>
                                <b style="font-size: 20px;">UNIT PENJAMINAN MUTU</b>
                                <br>
                                <span
                                    style="font-size: 16px; font-weight: 500">{{ $data['university']?->alamat}}</span>
                                <br>
                                <span style="font-size: 16px; font-weight: 500">Telp:
                                    {{ $data['university']?->telepon ?? '-' }} Email: {{ $data['university']?->email ?? '-'}}</span>
                            </p>
                        </th>
                    </tr>
                </thead>
            @endif
            <tbody>
                <tr>
                    <td colspan="1000" style="border: none !important">
                        <div class="report-title">
                            <p class="report-title__text">
                                <b style="font-size: 24px;">LAPORAN AUDIT MUTU INTERNAL</b>
                                <br>
                                <b style="font-size: 24px;">Daftar Temuan Audit Mutu Internal</b>
                            </p>
                        </div>

                        <div class="section-list">
                            <div class="table-info">
                                <table class="introduction">
                                    <tbody>
                                        <tr>
                                            <td colspan="2"><b>Periode AMI</b></td>
                                            <td class="introduction__value">{{ $data['self']->periode_audit ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Nama Kegiatan AMI</b></td>
                                            <td class="introduction__value">{{ $data['self']->nama_jadwal_audit ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Panduan Penilaian</b></td>
                                            <td class="introduction__value">{{ $data['self']->panduan_penilaian ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Fakultas/Departemen</b></td>
                                            <td class="introduction__value">{{ $data['self']->nama_fakultas ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Unit Kerja</b></td>
                                            <td class="introduction__value">{{ $data['self']->nama_prodi ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Jumlah Temuan</b></td>
                                            <td class="introduction__value">{{ count($data['temuan']) ?? '0' }}
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="table-temuan">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Butir</th>
                                            <th>Elemen Dan Indikator</th>
                                            <th>Kategori</th>
                                            {{-- <th>Jumlah Prodi</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['temuan'] ?? [] as $temuan)
                                            <tr>
                                                <td style="text-align: right;">{{$loop->iteration}}</td>
                                                <td>{{ $temuan['nomor_penilaian'] }}</td>
                                                <td>{{ $temuan['pertanyaan_penilaian'] }}</td>
                                                <td style="white-space: nowrap">
                                                    {{ $temuan['finding_type_name'] ?? '-' }}
                                                </td>
                                                {{-- <td style="text-align: right">{{ $temuan['total_finding'] }}</td> --}}
                                            </tr>
                                        @endforeach

                                        @if (empty($data['temuan']))
                                            <tr>
                                                <td colspan="1000" style="text-align: center">Tidak ada data</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
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
    </main>
</x-core::layouts.reports.show>
