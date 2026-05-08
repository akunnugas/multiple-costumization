@php
    use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;

    $jadwal = $item['original'] ?? null;
    if (!empty($jadwal)) {
        $waktu = \Modules\Core\Helpers\Date::formatDateTimeLong($jadwal['waktu_pelaksanaan']);
        if ($jadwal['tipe_kegiatan'] === PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_OFFLINE) {
            $tempat = $jadwal['tempat_pelaksanaan'] . ' ' . '(Offline)';
        } else {
            $link = $jadwal['link_presentasi_kegiatan'];
        }
    }
@endphp

@if(!empty($jadwal))
    <div class="util_d-flex util_flex-column util_gap-4px">
        <span>
            {{ $waktu }}
        </span>
        <span>
            @if(!empty($link))
                <a href="{{ $link }}" target="_blank" rel="noopener">{{ $link }}</a>
            @else
                {{ $tempat }}
            @endif
        </span>
    </div>
@endif
