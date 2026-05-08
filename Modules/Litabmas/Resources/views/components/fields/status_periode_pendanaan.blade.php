@if (!empty($data))
    @php
        $isAktif = $data['tanggal_mulai'] <= now() && $data['tanggal_akhir'] >= now();
    @endphp
    <x-core::badge :variant="$isAktif === true ? 'success' : 'danger'" type="secondary" size="sm">
        {{ $isAktif === true ? 'Aktif' : 'Tidak Aktif' }}
    </x-core::badge>
@endif
