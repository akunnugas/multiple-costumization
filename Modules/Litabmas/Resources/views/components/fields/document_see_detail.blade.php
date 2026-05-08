@php
    if (empty($data['temp_url']) && !empty($value)) { // ketika tidak ada temp_url (artinya belum ada proses get dari backend)
        $file = Modules\DMS\Models\Dokumen::where('id', $value)->first();
        $ext = $file->extension_versi_terbaru;
        $assetUrl = $ext ? asset("images/$ext-solid.svg") : null;
        $tempUrl = !empty($file) ? $file->lastVersionTemporaryUrl() : null;
    } elseif (!empty($data['temp_url'])) { // ketika sudah ada proses get dokumen dari backend
        $file = true;
        $assetUrl = $data['asset_url'] ?? null;
        $tempUrl = $data['temp_url'];
    }
@endphp
@if (!empty($file))
    <div class="util_d-flex">
        <a href="{{ $tempUrl }}" rel="noopener" target="blank" class="util_d-flex util_flex-center-vertical">
            @if(!empty($assetUrl))
                <img width="24px" src="{{ $assetUrl }}" alt="Dokumen">
            @endif
            &nbsp; Lihat File
        </a>
    </div>
@endif
