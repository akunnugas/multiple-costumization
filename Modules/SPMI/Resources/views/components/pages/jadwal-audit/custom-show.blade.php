<div class="box-table">
    <div class="box-table__content">
        <div class="table-max table-max_absolute">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Program Studi</th>
                        <th>Panduan Pengisian</th>
                        <th>Panduan Penilaian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['units'] as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->jenjang_pendidikan . ' - ' . $item->nama_unit }}</td>
                            <td>{{ $item->panduan_pengisian }}</td>
                            <td>{{ $item->panduan_penilaian }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
