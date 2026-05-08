@php
    use Carbon\Carbon;
    use Modules\SPMI\Models\AuditTemuan;
    use Modules\SPMI\Models\SuratTugasAuditorPegawai;

    $data['self'] = $data['self'] ?? null;

    if (!empty($data['self'])) {
        $title = 'Laporan Permintaan Tindakan Koreksi - ' . $data['self']->nama_prodi;
    } else {
        $title = 'Laporan Permintaan Tindakan Koreksi';
    }

    if (!empty($data['self']->tanggal_awal_penilaian)) {
        $auditDate =
            Carbon::parse($data['self']->tanggal_awal_penilaian)->translatedFormat('d F Y') .
            ' s.d. ' .
            Carbon::parse($data['self']->tanggal_akhir_penilaian)->translatedFormat('d F Y');
    }
@endphp

<x-core::layouts.reports.show :title="$title">
    @push('head')
        @vite('Modules/SPMI/Resources/assets/sass/reports/permintaan-tindakan-koreksi.scss')
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
                                    style="font-size: 16px; font-weight: 500">{{ $data['university']?->alamat }}</span>
                                <br>
                                <span style="font-size: 16px; font-weight: 500">Telp:
                                    {{ $data['university']?->telepon ?? '-' }} Email:
                                    {{ $data['university']?->email ?? '-' }}</span>
                            </p>
                        </th>
                    </tr>
                </thead>
            @endif
            <tbody>
                <tr>
                    <td colspan="1000" style="border: none !important">
                        {{-- <div class="label-form">
                            <span>Form 5</span>
                        </div> --}}
                        <div class="report-title">
                            <p class="report-title__text">
                                <b style="font-size: 24px;">LAPORAN AUDIT MUTU INTERNAL</b>
                                <br>
                                <b style="font-size: 24px;">Daftar Permintaan Tindakan Koreksi (PTK)</b>
                            </p>
                        </div>

                        <div class="section-list">
                            <div class="table-info">
                                <table class="introduction">
                                    <tbody>
                                        <tr>
                                            <td colspan="2"><b>Hari/Tanggal Audit</b></td>
                                            <td class="introduction__value">{{ $auditDate ?? '-' }}</td>
                                        </tr>
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
                                            <td colspan="2"><b>Program Pendidikan</b></td>
                                            <td class="introduction__value">{{ $data['self']->nama_jenjang ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Unit Kerja</b></td>
                                            <td class="introduction__value">{{ $data['self']->nama_prodi ?? '-' }}
                                            </td>
                                        </tr>

                                        {{-- TTD Ketua Prodi --}}
                                        <tr>
                                            <td rowspan="2"><b>Ketua Unit Kerja</b></td>
                                            <td><b>Nama</b></td>
                                            <td class="introduction__value">{{ $data['self']->kaprodi ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Tanda Tangan</b></td>
                                            <td style="height: 8rem"></td>
                                        </tr>

                                        {{-- TTD Ketua Auditor --}}
                                        <tr>
                                            <td rowspan="2"><b>Ketua Auditor</b></td>
                                            <td><b>Nama</b></td>
                                            <td class="introduction__value">{{ $data['self']->ketua_auditor ?? '-' }}
                                            </td>
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
                            </div>

                            <div class="table-temuan">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No. Butir</th>
                                            <th>Elemen Dan Indikator</th>
                                            <th>Target</th>
                                            <th>Capaian</th>
                                            <th>Kategori Temuan</th>
                                            <th>Temuan Audit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['temuan'] ?? [] as $temuan)
                                            <tr>
                                                <td>{{ $temuan->nomor_penilaian }}</td>
                                                <td>{{ $temuan->pertanyaan_penilaian }}</td>
                                                <td>{{ $temuan->nilai_target }}</td>
                                                <td>{{ $temuan->nilai_auditor }}</td>
                                                <td>{{ $temuan->jenis_temuan ? AuditTemuan::TYPES[$temuan->jenis_temuan] : '-' }}
                                                </td>
                                                <td>{{ $temuan->uraian_temuan_audit }}</td>
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
                            <div class="section-list__ol__li" style="padding-top: 30px">
                                <span class="title"><b>Akar Masalah</b></span>
                                <div class="italic-blue" style="margin-top: 6px">(Diisi oleh Auditor berdasarkan diskusi
                                    antara Auditee dan Auditor)
                                </div>
                                <ol class="desc-list-3">
                                    @if (!empty($data['akar_masalah']))
                                        @foreach ($data['akar_masalah'] as $akarMasalah)
                                            <li>
                                                <span>{{ $akarMasalah }}</span>
                                            </li>
                                        @endforeach
                                    @else
                                        <li>
                                            <span></span>
                                        </li>
                                        <li>
                                            <span></span>
                                        </li>
                                        <li>
                                            <span></span>
                                        </li>
                                    @endif
                                </ol>
                            </div>
                            <div class="section-list__ol__li">
                                <span class="title"><b>Rencana Tindakan Perbaikan dan Jadwal penyelesaian</b></span>
                                <div class="italic-blue" style="margin-top: 6px">(Diisi oleh Auditor berdasarkan diskusi
                                    antara Auditee dan Auditor)
                                </div>
                                <ol class="desc-list-3">
                                    @if (!empty($data['rencana_peningkatan_mutu']))
                                        @foreach ($data['rencana_peningkatan_mutu'] as $rencanaPeningkatanMutu)
                                            <li>
                                                <span>{{ $rencanaPeningkatanMutu }}</span>
                                            </li>
                                        @endforeach
                                    @else
                                        <li>
                                            <span></span>
                                        </li>
                                        <li>
                                            <span></span>
                                        </li>
                                        <li>
                                            <span></span>
                                        </li>
                                    @endif
                                </ol>
                            </div>
                            <div style="color: #000">
                                <b>PIC:</b>
                            </div>
                            <div style="color: #000">
                                <b>Deadline:</b>
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
