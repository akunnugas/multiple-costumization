@php use Modules\Litabmas\Models\KlasterPendanaan; @endphp
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

    // set jika kategori klaster adalah individu, maka unset field minimal & maksimal anggota
    foreach ($data[0]['items'] as $item) {
        if ($item['field'] === 'kategori_klaster' && $item['original'] === KlasterPendanaan::KATEGORI_INDIVIDU) {
            // Gunakan array_filter() untuk menghapus 'minimal_anggota' dan 'maksimal_anggota' dan 'apakah_butuh_approve_semua_anggota'
            $data[0]['items'] = array_filter($data[0]['items'], function($value) {
                return $value['field'] !== 'minimal_anggota' &&
                    $value['field'] !== 'maksimal_anggota' &&
                    $value['field'] !== 'apakah_butuh_approve_semua_anggota';
            });
            break;
        }
    }
@endphp
<x-core::layouts.main :$menu :$title :$subtitle :$action>
    <x-slot:sidebar>
        <x-core::layouts.outer.sidebar :data="$submenu"/>
    </x-slot:sidebar>
    <x-core::layouts.html.alert/>
    <x-core::layouts.detail.cards :$data/>

    <x-core::layouts.detail.card icon="document-text" title="Bidang Ilmu & Tema"
                                 subtitle="Daftar Bidang Ilmu & Tema yang digunakan pada Klaster Pendanaan ini">
        <x-core::table>
            @if(empty($bidangIlmuTemaMapping) || $bidangIlmuTemaMapping->isEmpty())
                <x-core::handler title="Belum Ada Data Bidang Ilmu & Tema"/>
            @else
                <div class="box-table">
                    <div class="box-table__content">
                        <div class="table-max" id="table-bidang-ilmu-tema-kegiatan">
                            <table>
                                <thead>
                                <tr>
                                    <th class="cell-check cell-center">No</th>
                                    <th>{{ __('litabmas::bidang_ilmu.nama_bidang_ilmu') }}</th>
                                    <th>{{ __('litabmas::tema_kegiatan.main') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($bidangIlmuTemaMapping as $value)
                                    @php
                                        $firstItem = $value->first();
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $firstItem->nama_bidang_ilmu }}
                                        </td>
                                        <td>
                                            @if($value->count() == 1)
                                                {{ $firstItem->nama_tema }}
                                            @else
                                                <ul>
                                                    @foreach($value as $item)
                                                        <li>{{ $item->nama_tema }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </x-core::table>
    </x-core::layouts.detail.card>

    <x-core::layouts.detail.card icon="document-plus" title="Output & Outcome"
                                 subtitle="Dibawah ini adalah Output & Outcome yang wajib dipilih peneliti">
        <x-core::table>
            @if(empty($outputMapping) || $outputMapping->isEmpty())
                <x-core::handler title="Belum Ada Data Output"/>
            @else
                <div class="box-table">
                    <div class="box-table__content">
                        <div class="table-max">
                            <table>
                                <thead>
                                <tr>
                                    <th class="cell-check cell-center">No</th>
                                    <th>{{ __('litabmas::jenis_output_penelitian.nama_output') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($outputMapping as $value)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $value->nama_output }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </x-core::table>

        <x-core::table>
            @if(empty($outcomeMapping) || $outcomeMapping->isEmpty())
                <x-core::handler title="Belum Ada Data Outcome"/>
            @else
                <div class="box-table">
                    <div class="box-table__content">
                        <div class="table-max">
                            <table>
                                <thead>
                                <tr>
                                    <th class="cell-check cell-center">No</th>
                                    <th>{{ __('litabmas::jenis_outcome_penelitian.nama_outcome') }}</th>
                                    <th>{{ __('litabmas::jenis_outcome_penelitian.batas_pengumpulan_outcome') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($outcomeMapping as $value)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $value->nama_outcome }}
                                        </td>
                                        <td>
                                            {{ $value->format_batas_pengumpulan_outcome }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </x-core::table>
    </x-core::layouts.detail.card>

    <x-core::layouts.detail.card icon="calendar-days" title="Tahapan Kegiatan"
                                 subtitle="Daftar Tahapan Kegiatan yang digunakan pada Klaster Pendanaan ini">
        <x-core::table>
            @if(empty($agendaMapping) || $agendaMapping->isEmpty())
                <x-core::handler title="Belum Ada Data Tahapan Kegiatan"/>
            @else
                <div class="box-table">
                    <div class="box-table__content">
                        <div class="table-max">
                            <table>
                                <thead>
                                <tr>
                                    <th class="cell-check cell-center">No</th>
                                    <th>{{ __('litabmas::agenda_kegiatan.nama_agenda') }}</th>
                                    <th>{{ __('litabmas::agenda_kegiatan.tanggal_agenda') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($agendaMapping as $value)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $value->nama_agenda }}
                                        </td>
                                        <td>
                                            {{ $value->tanggal_agenda ?? null }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </x-core::table>
    </x-core::layouts.detail.card>

    @pushonce('head')
        <style>
            .box-table__content {
                padding: 0 !important;
                border-top: none !important;
            }

            #table-bidang-ilmu-tema-kegiatan tbody tr {
                vertical-align: baseline;
            }

            td > ul {
                margin-left: 1rem;
            }
        </style>
    @endpushonce
</x-core::layouts.main>
