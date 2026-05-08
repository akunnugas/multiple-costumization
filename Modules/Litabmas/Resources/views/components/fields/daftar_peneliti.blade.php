@if(!empty($data['daftar_peneliti']))
    @php
        // pecah string menjadi array, utk yg pertama tambahkan flagging (Ketua)
        $daftarPeneliti = explode('; ', $data['daftar_peneliti']);
        $daftarPeneliti[0] = '(Ketua) ' . $daftarPeneliti[0];
    @endphp

    @if(count($daftarPeneliti) > 1)
        <ul style="margin-left: 4px">
            @foreach($daftarPeneliti as $peneliti)
                <li>{{ $peneliti }}</li>
            @endforeach
        </ul>
    @elseif (count($daftarPeneliti) == 1)
        {{ $daftarPeneliti[0] }}
    @endif
@endif
