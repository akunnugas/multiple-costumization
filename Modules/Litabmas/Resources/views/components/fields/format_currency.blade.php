@if(!empty($value))
    @php
        $defaultCurrency = config('money.defaults.currency');
        $currency = $data['mata_uang'] ?? $defaultCurrency;
        $convert = $currency !== $defaultCurrency;
    @endphp

    {{ money($value, $currency, $convert) }}
@endif
