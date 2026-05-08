<x-core::quantum-3.layouts.detail :data="$data">
    @foreach ($data as $id => $section)
        @if ($id == '_' || $id == '0')
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
            <div class="pt-3">
                <x-core::quantum-3.table.data :withSeparator="false" :showNumber="true" :header="[
                    ['field' => 'nama_kontak', 'label' => __('kerjasama::kontak.nama_kontak')],
                    ['field' => 'jabatan', 'label' => __('kerjasama::kontak.jabatan')],
                    ['field' => 'email', 'label' => __('kerjasama::kontak.email')],
                    ['field' => 'telepon', 'label' => __('kerjasama::kontak.telepon')],
                ]" :sortable="false" :data="$rawData->kontak">
                
                <x-slot:empty-element>
                    @php
                        $title = "Belum Ada Data kontak Mitra";
                        $subtitle = "Silakan menambah informasi kontak mitra melalui button 'Ubah Data'";
                    @endphp
                    <x-core::quantum-3.handler :$title :$subtitle :canCreate="false" />
                </x-slot:empty-element>
            </x-core::quantum-3.table.data>
            </div>
        </x-core::quantum-3.layouts.detail.card>
    @endforeach

</x-core::quantum-3.layouts.detail>