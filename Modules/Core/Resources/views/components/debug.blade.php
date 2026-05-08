@php
    $headerDB = [
        ['field' => 'count', 'label' => 'Jumlah', 'attributes' => ['style' => 'width:60px']],
        ['field' => 'time', 'label' => 'Detik']
    ];
    $dataDB = [
        'count' => 0,
        'time' => 0,
    ];

    $header = [
        ['field' => 'no', 'label' => 'No.', 'attributes' => ['style' => 'width:30px']],
        ['field' => 'time', 'label' => 'Detik', 'attributes' => ['style' => 'width:60px']],
        ['field' => 'query', 'label' => 'Query']
    ];

    $debug = [
        'Error' => session('debug'),
        'Main Database' => DB::getQueryLog(),
        'Shared Database' => DB::connection('shared')->getQueryLog(),
    ];

    if (!empty($debug['Error'])) {
        $headerError = [
            ['field' => 'query', 'label' => 'Query'],
            ['field' => 'message', 'label' => 'Error']
        ];
    }

    foreach ($debug as $name => $data) {
        if (empty($data)) {
            continue;
        }

        if ($name == 'Error') {
            $data = [$data];
        }

        foreach ($data as $k => $v) {
            $query = str_replace(['?', '"'], ['\'%s\'', ''], $v['query']);
            $query = preg_replace(['/\s+/', '/\s([?.!])/'], [' ', '$1'], $query);
            $query = vsprintf($query, $v['bindings']);

            if ($name == 'Error') {
                $data = [
                    'message' => $v['message'],
                    'query' => $query,
                ];

                break;
            }

            $sec = round($v['time'] / 1000, 4);
            $dataDB['time'] += $sec;
            $dataDB['count']++;

            $data[$k] = [
                'no' => $k + 1,
                'time' => $sec,
                'query' => $query,
            ];
        }

        $debug[$name] = $data;
    }
@endphp

<div class="grid">
    <x-core::table title="Rekapitulasi Query" class="col-1">
        <x-core::table.data :header="$headerDB" :data="[$dataDB]" />
    </x-core::table>
    @if (!empty($debug['Error']))
        <x-core::table class="col-11">
            <x-slot:title>
                <span style="color:#EC3D27">Error</span>
            </x-slot:title>
            <x-core::table.data :header="$headerError" :data="[$debug['Error']]" />
        </x-core::table>
    @endif
</div>

@foreach ($debug as $name => $data)
    @continue($name == 'Error')
    <x-core::table title="{{ $name }}">
        <x-core::table.data :$header :$data />
    </x-core::table>
@endforeach
