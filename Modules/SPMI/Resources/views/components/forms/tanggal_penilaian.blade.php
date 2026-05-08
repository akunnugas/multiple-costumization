@php
    $attributes['type'] = 'date';

    $startDateName = 'tanggal_awal_penilaian';
    $endDateName = 'tanggal_akhir_penilaian';

    // cek error
    $startDateError = $errors->has($startDateName);
    $endDateError = $errors->has($endDateName);
    $isError = $startDateError || $endDateError;
    if ($isError) {
        $helper = $errors->first($startDateName);
        $helper = empty($helper) ? $errors->first($endDateName) : $helper;
    }
    $value = $attributes['value'] ?? null;
    $values = explode('|', $value);
@endphp

@pushOnce('head')
    @vite('resources/scss/custom-utils.scss')
@endPushOnce

<div class="util_w-100">
    <div class="util_d-flex util_w-100">
        <div class="util_w-45">
            @php($attributes['name'] = $startDateName)
            @php($attributes['value'] = $values[0] ?? null)
            @php($attributes['wire:model'] = 'record.tanggal_awal_penilaian')
            <x-core::controls.input {{ $attributes }} />
        </div>
        <div class="util_w-10 util_d-flex util_flex-middle util_flex-center">
            s.d.
        </div>
        <div class="util_w-45">
            @php($attributes['name'] = $endDateName)
            @php($attributes['value'] = $values[1] ?? null)
            @php($attributes['wire:model'] = 'record.tanggal_akhir_penilaian')
            <x-core::controls.input {{ $attributes }} />
        </div>
    </div>
    <div @class(['form-control__helper', 'error' => $isError])>
        {{ $helper ?? null }}
    </div>
</div>
