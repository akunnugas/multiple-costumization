@php use Modules\Litabmas\Models\PengajuanPendanaanStatus; @endphp
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
    $resourceTitle = $title;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $similarityAiMemenuhiSyarat = $infoSimilariyAI['similarity']['melebihi_toleransi'] === false &&
        $infoSimilariyAI['ai']['melebihi_toleransi'] === false;
    $validasiDokumenBelumDilakukan = $statusDokumenLengkap = false;
    if ($rawData['status_penilaian_administrasi'] === PengajuanPendanaanStatus::PENILAIAN_ADMINISTRASI_BELUM_DIVALIDASI) {
        $validasiDokumenBelumDilakukan = true;
    } elseif ($rawData['status_penilaian_administrasi'] !== PengajuanPendanaanStatus::PENILAIAN_ADMINISTRASI_TIDAK_LOLOS_DOKUMEN) {
        $statusDokumenLengkap = true;
    }

    // Tambahkan pengecekan status administrasi/peninjauan agar tombol muncul setelah proposal lolos administrasi/peninjauan
    $isLolosAdministrasi = in_array($rawData['status_agenda_kegiatan'], [
        \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI,
        \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL7_PENINJAUAN_PROPOSAL
    ]);
    $bisaUpdateReviewer = ($statusDokumenLengkap && $similarityAiMemenuhiSyarat) || $isLolosAdministrasi;
    $isMaxReviewer = $reviewers->count() >= \Modules\Litabmas\Models\PengajuanPendanaanReviewerAdministrasi::MAX_REVIEWER_ADMINISTRASI;
    if (!$bisaUpdateReviewer) {
        $staticAlert = [
            'message' => 'Anda tidak dapat menambahkan reviewer karena penilaian similarity dan AI tidak memenuhi
                kriteria.',
            'type' => 'warning',
            'dismissible' => false
        ];
    } elseif ($isMaxReviewer) {
        $staticAlert = [
            'message' => 'Anda tidak dapat menambahkan reviewer karena jumlah reviewer sudah mencapai batas maksimal.',
            'type' => 'helper',
            'dismissible' => false
        ];
    } else {
        $staticAlert = [
            'message' => 'Setelah memvalidasi dokumen dan memberikan penilaian pada proposal ini,
                Anda dapat menambahkan reviewer.',
            'type' => 'helper',
            'dismissible' => false
        ];
    }
@endphp

@pushonce('head')
    @vite('resources/scss/custom-utils.scss')
@endpushonce

<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu"/>
    </x-slot:sidebar>

    <x-core::layouts.html.alert/>

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$data[0]['items']"/>
        </div>
    </div>

    @if(!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert"/>
    @endif

    <x-litabmas::layouts.detail.card title="Tambahkan Reviewer"
                                     customClassBody="util_d-flex util_flex-column util_gap-1"
                                     subtitle="Silahkan tambahkan reviewer untuk memberikan penilaian pada proposal ini">

        @if($bisaUpdateReviewer && !$isMaxReviewer)
            <x-slot:action>
                <div class="util_d-flex">
                    <x-core::button href="#" size="sm" data-toggle="modal" data-target="#modal-tambah-reviewer">
                        Tambahkan Reviewer
                    </x-core::button>
                </div>
            </x-slot:action>
        @endif

        <div class="box-table__content">
            <div class="table-max">
                <table>
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Reviewer</th>
                        <th>Dokumen SK Reviewer</th>
                        <th class="cell-action cell-center">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @foreach($reviewers as $reviewer)
                        @php
                            $namaLengkap = $reviewer['nip'] . ' - ' . $reviewer['nama'];
                            $encoded = base64_encode(
                                json_encode([
                                    'id' => $reviewer['id'],
                                    'id_biodata' => $reviewer['id_biodata'],
                                    'text' => $namaLengkap,
                                ]),
                            );
                        @endphp
                        <tr>
                            <td width="10">{{ $no++ }}.</td>
                            <td>{{ $namaLengkap }}</td>
                            <td class="util_d-flex util_flex-center-vertical">
                                @if($reviewer['id_dokumen_sk'])
                                    <img height="20px;" src="{{ $reviewer->dokumen_sk->asset_url }}"
                                         alt="Dokumen Reviewer {{ $namaLengkap }}">
                                    &nbsp; {{ $reviewer->dokumen_sk->nama_dokumen }}
                                @endif
                            </td>
                            <td class="cell-action">
                                <div class="dropdown-group">
                                    @if($bisaUpdateReviewer)
                                        <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                                                        href="javascript:showModal('{{ $encoded }}')"
                                                        data-btn-label="Edit Reviewer"/>
                                        <x-core::button leading-icon="trash-solid" variant="outline" size="xs"
                                                        href="javascript:deleteRecord('{{ $encoded }}')"
                                                        data-btn-label="Hapus Reviewer"/>
                                    @else
                                        <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                                                        disabled/>
                                        <x-core::button leading-icon="trash-solid" variant="outline" size="xs"
                                                        disabled/>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if($reviewers->isEmpty())
                    @php
                        $emptyTitle = "Belum Ada Data Reviewer";
                        $emptySubtitle = "Silakan tambahkan data Reviewer dengan cara klik tombol tambahkan reviewer";
                    @endphp
                    <x-core::handler title="{!! $emptyTitle !!}" subtitle="{!! $emptySubtitle !!}" :canCreate="false"/>
                @endif
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    @if($bisaUpdateReviewer)
        {{-- Modal --}}
        <x-litabmas::pages.assign-reviewer.modal-show-page :$dosenOption/>
    @endif

    @pushonce('head')
        <style>
            .col-12.col-sm-8.col-md-9.col-lg-9 {
                display: flex;
                align-items: flex-start;
                gap: 0.25rem;
            }

            table .icon.icon-x-circle-solid { /* yg check nggk pelru karena udh ada di qn.css nya*/
                color: var(--qn-danger);
                font-size: 1.5rem;
            }

            .box-table__content {
                border-top: none;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
