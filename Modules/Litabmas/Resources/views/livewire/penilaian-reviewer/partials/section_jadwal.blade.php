<div class="card card_details-custom">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h3>Jadwal Presentasi</h3>
            <p style="color: #697586; margin-top: 5px;">
                Jadwal presentasi akan tampil apabila sudah dibuat oleh Admin LPPM
            </p>
        </div>
    </div>
    <hr>

    <div class="box-table__content" id="table-docs" style="border: 0px;">
        <div class="table-max">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kegiatan</th>
                        <th>Tanggal Presentasi</th>
                        <th style="width: 35%;">Lokasi / Link Kegiatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataJadwalProposal as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_kegiatan }}</td>
                            <td>{{ Carbon\Carbon::parse($item->waktu_pelaksanaan)->translatedFormat('d F Y H:i') }} WIB
                            </td>
                            <td>
                                @if ($item->link_presentasi_kegiatan)
                                    <a href="{{ $item->link_presentasi_kegiatan }}" target="_blank"
                                        rel="noopener noreferrer">{{ $item->link_presentasi_kegiatan }}</a>
                                @else
                                    {{ $item->tempat_pelaksanaan }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if (count($dataJadwalProposal) == 0)
        <div class="empty-list">
            <div class="empty-list__wrapper">
                <div class="empty-list__content" style="align-items:center;">
                    <div class="empty-list__inner">
                        <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                        <h1 style="text-align: start;">Belum ada Jadwal Presentasi</h1>
                        <p style="text-align: start; max-width: 550px;">
                            Belum ada jadwal presentasi yang ditambahkan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<br>
