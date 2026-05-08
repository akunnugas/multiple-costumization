<x-core::quantum-3.layouts.detail :data="$data">
    @foreach ($data as $id => $section)
        @if ($id == '_')
            @continue
        @endif
        @php
            $attributes = Page::buildAttributes(Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']]);
        @endphp
        <x-core::quantum-3.layouts.detail.card {{ $attributes }}>
            <x-slot:action>
                @if (!empty($section['edit_url']))
                    <x-core::quantum-3.button 
                        href="{{ $section['edit_url'] }}" 
                        variant="light" 
                        leading-icon="{{ $section['edit_icon'] ?? 'edit-02' }}"
                    >
                        {{ $section['edit_label'] ?? 'Ubah Data' }}
                    </x-core::quantum-3.button>
                @endif
            </x-slot:action>
            @php
                $data = $section['items'];
                if (count($data) >= 8) {
                    $half = ceil(count($data) / 2);
                    $dataChunks = array_chunk($data, $half);
                }
            @endphp

            @if (!empty($dataChunks))
                @foreach ($dataChunks as $card)
                    <div class="col-md-6">
                        <div class="row gx-1 gy-3">
                            @if (!isset($pageConf['custom_page']) || empty($pageConf['custom_page']))
                                <x-core::quantum-3.layouts.detail.line :data="$card" />
                            @else
                                @php
                                    $customPage = $pageConf['custom_page'];
                                    $customPageData = $pageConf['custom_page_data'] ?? [];
                                @endphp
                                <x-core::layouts.detail.line :data="$card" :$customPage :$customPageData />
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
            <div class="col-md-6">
                <div class="row gx-1 gy-3">
                    @if (!isset($pageConf['custom_page']) || empty($pageConf['custom_page']))
                        <x-core::quantum-3.layouts.detail.line :$data />
                    @else
                        @php
                            $customPage = $pageConf['custom_page'];
                            $customPageData = $pageConf['custom_page_data'] ?? [];
                        @endphp
                        <x-core::layouts.detail.line :$data :$customPage :$customPageData />
                    @endif
                </div>
            </div>
            @endif

            @php
                $headerIndikator = [];
            @endphp
            <div class="pt-3">
                <div class="table-responsive mb-3 pb-1">
                    <table class="table table-bordered align-middle mb-0" x-data="">
                        <thead class="align-middle">
                            <tr class="table-light">
                                <th class="cell-check text-center">No</th>
                                @foreach ($headerSasaran as $i => $item)
                                    @php            
                                        $attributes = Page::buildAttributes($item['attributes'] ?? null);
            
                                        $label = $item['label'] ?? null;
                                        if (empty($label) && !empty($item['field'])) {
                                            $label = Page::defineLabelByField($item['field'], $urlInfo ?? null);
                                        }
            
                                        $class = [
                                            'text-nowrap',
                                        ];
                                    @endphp
                                    <th {{ $attributes->class($class) }} @style([
                                        'cursor: pointer',
                                    ])>
                                        <div class="d-flex gap-3">
                                            {{ $label }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                
                        <tbody>
                            @foreach ($rawData->sasaran as $indexMapping => $mapping)
                                @php
                                    $sasaranKinerja = $mapping->sasaranKinerja;
                                    $indikator = $sasaranKinerja->indikator;
                                    $indikatorCount = $indikator->count();
                                @endphp
                                
                                @foreach ($indikator as $index => $item)
                                    <tr>
                                        @if ($index === 0)
                                            <td rowspan="{{ $indikatorCount }}" class="text-center">{{ $indexMapping+1 }}</td>
                                            <td rowspan="{{ $indikatorCount }}">{{ $sasaranKinerja->sasaran }}</td>
                                            <td rowspan="{{ $indikatorCount }}">{{ $sasaranKinerja->keterangan }}</td>
                                            <td rowspan="{{ $indikatorCount }}">{{ $sasaranKinerja->level }}</td>
                                        @endif
                                        <td>{{ $item->indikator }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        
                    </table>
                    @if ($rawData->sasaran->isEmpty())
                        @php
                            $title = "Belum Ada Data ".__('kerjasama::sasaran_kinerja.main');
                            $subtitle = "Silakan menambah informasi ".__('kerjasama::sasaran_kinerja.main')." melalui button 'Ubah Data'";
                        @endphp
                        <x-core::quantum-3.handler :$title :$subtitle :canCreate="false" />
                    @endif
                </div>
                
            </div>
        </x-core::quantum-3.layouts.detail.card>
    @endforeach

</x-core::quantum-3.layouts.detail>