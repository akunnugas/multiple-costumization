@php
    use Modules\Litabmas\Models\PengajuanPendanaanStatus;
@endphp
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

    // handle $data
    $primaryData ??= $data['primary-section']['items'];
    unset($data['primary-section']);

    // get status_penentuan_pendanaan from field on array $primaryData
    $statusPenentuanPendanaan =
        collect($primaryData)->firstWhere('field', 'status_penentuan_pendanaan')['original'] ?? null;

    $bisaUpdatePembimbing = $statusPenentuanPendanaan == PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_LOLOS_PENDANAAN;
    $isMaxPembimbing = $pembimbings->count() >= \Modules\Litabmas\Models\PengajuanPendanaanPembimbing::MAX_PEMBIMBING;
    if (!$bisaUpdatePembimbing) {
        $staticAlert = [
            'message' => 'Tidak dapat menambahkan pembimbing. Status proposal saat ini belum ditentukan.',
            'type' => 'warning',
            'dismissible' => false,
        ];
        if ($statusPenentuanPendanaan == PengajuanPendanaanStatus::PENENTUAN_PENDANAAN_TIDAK_LOLOS_PENDANAAN) {
            $staticAlert = [
                'message' =>
                    'Anda tidak dapat menambahkan pembimbing karena proposal ini tidak lolos penentuan pendanaan.',
                'type' => 'danger',
                'dismissible' => false,
            ];
        }
    } elseif ($isMaxPembimbing) {
        $staticAlert = [
            'message' =>
                'Anda tidak dapat menambahkan pembimbing karena jumlah pembimbing sudah mencapai batas maksimal.',
            'type' => 'helper',
            'dismissible' => false,
        ];
    } else {
        $staticAlert = [
            'message' => 'Setelah proposal ini lolos penentuan pendanaan,
                Anda dapat menambahkan pembimbing.',
            'type' => 'helper',
            'dismissible' => false,
        ];
    }
@endphp

@pushonce('head')
    @vite('resources/scss/custom-utils.scss')
@endpushonce

<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu" />
    </x-slot:sidebar>

    <x-core::layouts.html.alert />

    <div class="card card_details-primary">
        <div class="grid">
            <x-litabmas::layouts.detail.line :data="$primaryData" />
        </div>
    </div>

    @if (!empty($staticAlert))
        <x-core::layouts.html.alert :data="$staticAlert" />
    @endif

    <x-litabmas::layouts.detail.card title="Tambahkan Pembimbing"
        customClassBody="util_d-flex util_flex-column util_gap-1"
        subtitle="Tambahkan pembimbing untuk penilaian proposal setelah status proposal ditentukan.">

        @if ($bisaUpdatePembimbing && !$isMaxPembimbing)
            <x-slot:action>
                <div class="util_d-flex">
                    <x-core::button href="#" size="sm" data-toggle="modal"
                        data-target="#modal-tambah-pembimbing">
                        Tambahkan Pembimbing
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
                            <th>Nama Pembimbing</th>
                            <th>Dokumen SK Pembimbing</th>
                            <th class="cell-action cell-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                        @endphp
                        @foreach ($pembimbings as $pembimbing)
                            @php
                                $namaLengkap = $pembimbing['nip'] . ' - ' . $pembimbing['nama'];
                                $encoded = base64_encode(
                                    json_encode([
                                        'id' => $pembimbing['id'],
                                        'id_biodata' => $pembimbing['id_biodata'],
                                        'text' => $namaLengkap,
                                    ]),
                                );
                            @endphp
                            <tr>
                                <td width="10">{{ $no++ }}.</td>
                                <td>{{ $namaLengkap }}</td>
                                <td class="util_d-flex util_flex-center-vertical">
                                    @if ($pembimbing['id_dokumen_sk'])
                                        <img height="20px;" src="{{ $pembimbing->dokumen_sk->asset_url }}"
                                            alt="Dokumen Pembimbing {{ $namaLengkap }}">
                                        &nbsp; {{ $pembimbing->dokumen_sk->nama_dokumen }}
                                    @endif
                                </td>
                                <td class="cell-action">
                                    <div class="dropdown-group">
                                        @if ($bisaUpdatePembimbing)
                                            <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                                                href="javascript:showModal('{{ $encoded }}')"
                                                data-btn-label="Edit Pembimbing" />
                                            <x-core::button leading-icon="trash-solid" variant="outline" size="xs"
                                                href="javascript:deleteRecord('{{ $encoded }}')"
                                                data-btn-label="Hapus Pembimbing" />
                                        @else
                                            <x-core::button leading-icon="pencil-solid" variant="outline" size="xs"
                                                disabled />
                                            <x-core::button leading-icon="trash-solid" variant="outline" size="xs"
                                                disabled />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($pembimbings->isEmpty())
                    @php
                        $emptyTitle = 'Belum ada data pembimbing.';
                        $emptySubtitle =
                            'Silakan tambahkan data Pembimbing dengan cara klik tombol tambahkan pembimbing';
                        if ($staticAlert) {
                            $emptySubtitle = 'Data pembimbing akan ditampilkan setelah Anda menambahkan pembimbing.';
                        }
                    @endphp
                    <x-core::handler title="{!! $emptyTitle !!}" subtitle="{!! $emptySubtitle !!}"
                        :canCreate="false" />
                @endif
            </div>
        </div>
    </x-litabmas::layouts.detail.card>

    @if ($bisaUpdatePembimbing)
        {{-- Modal --}}
        <x-litabmas::pages.assign-pembimbing.modal-show-page :$dosenOption />
    @endif

    @pushonce('head')
        <style>
            .col-12.col-sm-8.col-md-9.col-lg-9 {
                display: flex;
                align-items: flex-start;
                gap: 0.25rem;
            }

            table .icon.icon-x-circle-solid {
                /* yg check nggk pelru karena udh ada di qn.css nya*/
                color: var(--qn-danger);
                font-size: 1.5rem;
            }

            .box-table__content {
                border-top: none;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
