@if(!empty($value))
    @php
        $listBertugas = array_filter(explode(',', $value));
        $iteration = 1;
    @endphp

    @foreach ($listBertugas as $cVal)
        @php
            $cVal = trim($cVal);
            if (empty($cVal))
                continue;

            $parts = explode('(', $cVal);
            $realVal = $parts[0] ?? '-';

            $tanggal = [];
            if (isset($parts[1])) {
                $dateStr = str_replace(')', '', $parts[1]);
                $tanggal = explode(' - ', $dateStr);
            }
        @endphp

        <div style="margin-bottom: 8px;">
            {{ $iteration }}. {{ trim($realVal) }}

            @if(count($tanggal) === 2 && trim($tanggal[0]) !== '' && trim($tanggal[1]) !== '')
                <br>
                <small class="text-muted" style="color: #6c757d;">
                    ({{ \Carbon\Carbon::parse(trim($tanggal[0]))->translatedFormat('d M Y') }} -
                    {{ \Carbon\Carbon::parse(trim($tanggal[1]))->translatedFormat('d M Y') }})
                </small>
            @endif
        </div>

        @php $iteration++; @endphp
    @endforeach
@else
    <span class="text-muted">Belum ada jadwal tugas</span>
@endif
