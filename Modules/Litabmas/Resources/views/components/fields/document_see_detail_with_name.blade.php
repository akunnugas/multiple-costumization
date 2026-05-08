@php
    if (empty($data['temp_url']) && !empty($value)) { // ketika tidak ada temp_url (artinya belum ada proses get dari backend)
        $file = Modules\DMS\Models\Dokumen::where('id', $value)
            ->select('nama_dokumen', 'alamat_versi_terbaru')
            ->first();
        $ext = $data['extension_versi_terbaru'] ?? null;
        $assetUrl = asset("images/$ext-solid.svg");
        $tempUrl = !empty($file) ? $file->lastVersionTemporaryUrl() : null;
        $namaDokumen = $file?->nama_dokumen . '.' . $ext;
    } elseif (!empty($data['temp_url'])) {
        $file = true;
        $assetUrl = $data['asset_url'] ?? null;
        $tempUrl = $data['temp_url'];
        $namaDokumen = $data['nama_dokumen'] ?? null;
    }
@endphp
@if (!empty($file))
    <div class="util_d-flex util_flex-center-vertical util_gap-4px">
        <a href="{{ $tempUrl }}" rel="noopener" target="blank" class="util_d-flex util_flex-center-vertical">
            <img width="24px" src="{{ $assetUrl }}" alt="Dokumen">
            &nbsp; {{ $namaDokumen }}
        </a>
    </div>
@endif
