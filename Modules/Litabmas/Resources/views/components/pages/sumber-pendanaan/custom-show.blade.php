<br><br>
<h3>Tahapan Kegiatan</h3>
<br>
<table>
    <thead>
        <tr>
            <th class="cell-check cell-center">No</th>
            <th>Tahapan Kegiatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['sourceAgendaMapping'] as $agenda)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $agenda->nama_agenda }}</td>
            </tr>
        @endforeach

        @if (count($data['sourceAgendaMapping']) == 0)
            <tr>
                <td colspan="2">Belum ada data agenda kegiatan</td>
            </tr>
        @endif
    </tbody>
</table>

<br>
<p>*Pengisian tanggal tahapan kegiatan dilakukan pada bagian klaster pendanaan</p>
