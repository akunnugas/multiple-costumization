@if (!empty($data['waktu_mulai_pendaftaran']) || !empty($data['waktu_selesai_pendaftaran']))
    @if (!empty($data['waktu_mulai_pendaftaran']))
        {{ Carbon\Carbon::parse($data['waktu_mulai_pendaftaran'])->translatedFormat('d M Y') }}
    @endif

    @if (!empty($data['waktu_selesai_pendaftaran']))
        @if (!empty($data['waktu_mulai_pendaftaran']))
            - <br>
        @endif
        {{ Carbon\Carbon::parse($data['waktu_selesai_pendaftaran'])->translatedFormat('d M Y') }}
    @endif
@endif

@if (empty($data['waktu_mulai_pendaftaran']) && empty($data['waktu_selesai_pendaftaran']))
    <span class="text-muted">Belum Ditentukan</span>
@endif
