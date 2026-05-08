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

            <div class="pt-3">
                <x-core::quantum-3.table.data :withSeparator="false" :showNumber="true" :header="[
                    ['field' => 'indikator', 'label' => __('kerjasama::indikator_sasaran.indikator')],
                    ['field' => 'keterangan', 'label' => __('kerjasama::indikator_sasaran.keterangan')],
                    ['field' => 'volume', 'label' => __('kerjasama::indikator_sasaran.volume')],
                    ['field' => 'satuan', 'label' => __('kerjasama::indikator_sasaran.satuan')],
                ]" :sortable="false" :data="$rawData->indikator">
                
                <x-slot:empty-element>
                    @php
                        $title = "Belum Ada Data ".__('kerjasama::indikator_sasaran.main');
                        $subtitle = "Silakan menambah informasi ".__('kerjasama::indikator_sasaran.main')." melalui button 'Ubah Data'";
                    @endphp
                    <x-core::quantum-3.handler :$title :$subtitle :canCreate="false" />
                </x-slot:empty-element>
            </x-core::quantum-3.table.data>
            </div>
        </x-core::quantum-3.layouts.detail.card>
    @endforeach

</x-core::quantum-3.layouts.detail>