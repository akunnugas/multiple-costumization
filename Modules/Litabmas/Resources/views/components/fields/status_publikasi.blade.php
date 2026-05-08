@php
    use Modules\Litabmas\Enums\StatusPublikasiEnum;

    if ($value === StatusPublikasiEnum::STATUS_PUBLIKASI_SELESAI) {
        $variant = 'success';
        $value = StatusPublikasiEnum::STATUS_PUBLIKASI_OPTIONS[$value];
    } elseif ($value === StatusPublikasiEnum::STATUS_PUBLIKASI_DIBLOKIR) {
        $variant = 'danger';
        $value = StatusPublikasiEnum::STATUS_PUBLIKASI_OPTIONS[$value];
    }
@endphp

@if(!empty($value))
    <x-core::badge :variant="$variant" type="outline" size="sm">
        {{ $value }}
    </x-core::badge>
@endif
