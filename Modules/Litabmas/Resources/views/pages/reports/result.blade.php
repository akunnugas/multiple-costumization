@props([
    'data' => [],
    'title' => null,
    'pageback' => false,
])
@php
    $indicators = $data;
@endphp
<x-core::layouts.reports.show :title="$title" :pageback="$pageback">
    @push('head')
        @vite('Modules/Litabmas/Resources/assets/sass/reports/show.scss')
        @vite('Modules/Litabmas/Resources/assets/js/reports/show.js')
        <style>
            .subtitle {
                font-weight: bold;
                margin-bottom: 0;
            }
        </style>
    @endpush
    @if ($isUsingKop)
    <section>
        <div id="kop" class="kop">
            <div class="logo">
                <img src="{{ session('token.logo_univ') ?? Page::quantumAsset('images/logo-kampus.png') }}" style="max-height: 70px;" alt="Logo Universitas" />
            </div>
            <div class="univ-info">
                <h1>{{ $dataUnivV1['nama'] }}</h1>
                <p>{{ $dataUnivV1['alamat'] }}</p>
            </div>
        </div>
    </section>
    @endif
    <br/>
    <div class="content">
        <div class="page page-center page-information lk-page">
            <h4><?= $title ?></h4>
            <br>
        </div>
        <div class="page">
            <div class="header-report">
                <?php foreach ($header as $key => $value) { ?>
                        <div class="header-title">
                            <label class="row-data__name"><?= $key ?></label>
                        </div>
                        <div class="header-value">
                            <span class="row-data__value" style="display: inline-block; overflow-wrap: anywhere;">
                                <span class="row-data__colon">:</span>
                                <?= $value ?>
                            </span>
                        </div>
                <?php } ?>
            </div>
            <br/>
            <table class="table">
                <thead>
                    <tr>
                        <?php foreach ($columns as $key => $value) {
                            echo '<th>' . $value['label'] . '</th>';
                        } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (count($data) === 0) {
                            echo '<tr><td style="text-align: center" colspan="'. count($columns) .'">Data tidak ditemukan</td></tr>';
                        } else {
                            foreach ($data as $key => $value) {
                                ?>
                            <tr>
                                <?php foreach ($columns as $column) {
                                    if ($column['field'] == 'no') {
                                        echo '<td>' . ($key + 1) . '</td>';
                                    } else {
                                        if (!empty($column['currency_field'])) {
                                            $originalValue = $value->{$column['field']} ?? null;
                                            $currencyValue = $value->{$column['currency_field']} ?? null;
                                            $convert = $currencyValue !== config('money.defaults.currency');
                                            $v = money($originalValue, $currencyValue, $convert);
                                        } else if (!empty($column['options'])) {
                                            $v = $column['options'][$value->{$column['field']}] ?? null;
                                        } else {
                                            $v = $value->{$column['field']} ?? null;
                                        }
                                        echo '<td>' . (is_array($v) ? implode(', ', $v) : ($v ?? '')) . '</td>';
                                    }
                                } ?>
                            </tr>
                        <?php }
                        } ?>
                </tbody>
            </table>
        </div>
    </div>
</x-core::layouts.reports.show>
