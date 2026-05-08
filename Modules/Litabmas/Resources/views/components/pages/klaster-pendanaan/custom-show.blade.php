{{-- Bidang Ilmu --}}
<br><br>
<h3>Bidang Ilmu dan Tema</h3>
<br>
<table>
    <thead>
        <tr>
            <th class="cell-check cell-center">No</th>
            <th>Nama Bidang Ilmu</th>
            <th>Tema</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['bidangIlmuTemaMapping'] as $parentLabel => $childs)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $parentLabel }}</td>
                <td>
                    @foreach ($childs as $label)
                        <li>{{ $label }}</li>
                    @endforeach
                </td>
            </tr>
        @endforeach

        @if (count($data['bidangIlmuTemaMapping']) == 0)
            <tr>
                <td colspan="3">Belum ada data bidang ilmu dan tema</td>
            </tr>
        @endif
    </tbody>
</table>
<hr><br>

<div style="display: flex; gap: 10px;">
    <div style="width: 50%">
        <h3>Luaran yang wajib dipillih</h3>
        <br>
        <table>
            <thead>
                <tr>
                    <th class="cell-check cell-center">No</th>
                    <th>Nama Luaran</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['outputMapping'] as $output)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $output->nama_output }}</td>
                    </tr>
                @endforeach

                @if (count($data['outputMapping']) == 0)
                    <tr>
                        <td colspan="2">Belum ada data luaran</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div style="width: 50%">
        <h3>Publikasi yang wajib dipilih</h3>
        <br>
        <table>
            <thead>
                <tr>
                    <th class="cell-check cell-center">No</th>
                    <th>Nama Publikasi</th>
                    <th>Batas Pengumpulan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['outcomeMapping'] as $outcome)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $outcome->nama_outcome }}</td>
                        <td>{{ $outcome->format_batas_pengumpulan_outcome }}</td>
                    </tr>
                @endforeach

                @if (count($data['outcomeMapping']) == 0)
                    <tr>
                        <td colspan="3">Belum ada data publikasi</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
<hr><br>

<h3>Tahapan Kegiatan</h3>
<br>
<table>
    <thead>
        <tr>
            <th class="cell-check cell-center">No</th>
            <th>Tahapan Kegiatan</th>
            <th>Tanggal Tahapan Kegiatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['agendaMapping'] as $agenda)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $agenda->nama_agenda }}</td>
                <td>{{ $agenda->tanggal_agenda ?? '-' }}</td>
            </tr>
        @endforeach

        @if (count($data['agendaMapping']) == 0)
            <tr>
                <td colspan="3">Belum ada data agenda kegiatan</td>
            </tr>
        @endif
    </tbody>
</table>
