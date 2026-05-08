@php
    $encoded = base64_encode(
        json_encode([
            'id' => $data['id'],
            'text' => $data['pertanyaan_penilaian'] ?? ($value ?? $definer),
        ]),
    );
@endphp
<div class="dropdown-group" style="display: flex; align-items: center; gap: 4px;"><a
        href="{{ route('spmi.penilaian-matriks.show', $data['id']) }}" class="btn btn_outline btn_xs"
        style="">
        <span class="icon icon-eye-solid"></span>
    </a>
</div>
