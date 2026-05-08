<div class="alert alert_helper">
    <div class="alert__content">
        <p>
            Afirmasi adalah proposal yang dinyatakan lolos oleh LPPM walaupun memiliki nilai bobot tidak memenuhi standar minimum
        </p>
    </div>
    <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
</div>
<br>

<div class="card card_details-custom">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h3>Proposal yang mendapatkan pendanaan berdasarkan klaster pendanaan</h3>
        </div>
        @php
            $optionFilterKlaster = Modules\Litabmas\Models\KlasterPendanaan::options();
        @endphp
        <div style="width: 30%;">
            <x-core::controls.select label="Klaster Pendanaan" purpose="filter"
                wire:model.live="filterKlaster" :options="$optionFilterKlaster" :value="$filterKlaster"
                :selected="empty($filterKlaster) ? 'all' : $filterKlaster" />
        </div>
    </div>
    <br>

    <div class="box-table__content" id="table-docs" style="border: 0px;">
        <div class="table-max">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Proposal</th>
                        <th>Anggota Proposal</th>
                        <th>Klaster Pendanaan</th>
                        <th>Biaya Disetujui</th>
                        <th class="cell-action cell-center" style="width: 10%;">Apakah Afirmasi?</th>
                        <th class="cell-action cell-center" style="width: 5%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataProposal as $item)
                        <tr style="vertical-align: top;">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->judul_proposal }}</td>
                            <td>
                                <div style="margin: 5px;">
                                    @php
                                        $listAnggota = explode(',', $item->nama_anggota);

                                        usort($listAnggota, function ($a, $b) {
                                            return strpos($a, '(Ketua)') !== false ? -1 : 1;
                                        });
                                    @endphp
                                    <ul>
                                        @foreach ($listAnggota as $anggota)
                                            <li>{{ trim($anggota) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                            <td>{{ $item->nama_klaster }}</td>
                            <td>
                                <div style="text-align: end">
                                    {{ money($item->nominal_anggaran_disetujui) }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    {{ $item->apakah_afirmasi ? checkListSVG() : crossListSVG() }}
                                </div>
                            </td>
                            <td class="cell-action cell-center">
                                <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;">
                                    <button type="button" class="btn btn_outline btn_xs" onclick="location.href = '{{ route('litabmas.pengajuan-pendanaan.show', $item->id) }}/overview'">
                                        <span class="icon icon-eye"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if (count($dataProposal) == 0)
    <div class="empty-list">
        <div class="empty-list__wrapper">
            <div class="empty-list__content" style="align-items:center;">
                <div class="empty-list__inner">
                    <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                    <h1 style="text-align: start;">Belum ada proposal yang didanai</h1>
                    <p style="text-align: start; max-width: 550px;">
                        Proposal yang telah disetujui oleh LPPM dan mendapatkan pendanaan akan muncul di sini
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
