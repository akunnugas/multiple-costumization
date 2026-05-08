@php
    $header = [
        ['field' => 'nama_klaster', 'label' => 'Nama Klaster', 'text' => $headerInfoKlaster['nama_klaster']],
        ['field' => 'jenis_pendanaan', 'label' => 'Jenis Pendanaan', 'text' => str_replace('_', ' ', ucfirst($headerInfoKlaster['jenis_pendanaan']))],
        ['field' => 'nama_sumber', 'label' => 'Sumber Pendanaan', 'text' => $headerInfoKlaster['nama_sumber']],
        ['field' => 'pengelola', 'label' => 'Pengelola Pendanaan', 'text' => $headerInfoKlaster['pengelola']],
    ];
@endphp

<div class="card card_details-primary">
    <div class="grid">
        <x-litabmas::layouts.detail.line :data="$header" />
    </div>
</div>
