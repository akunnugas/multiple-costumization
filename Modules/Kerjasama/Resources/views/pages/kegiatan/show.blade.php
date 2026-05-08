@php
    use Modules\Kerjasama\Models\PihakPenanggungJawab;
    use Modules\Core\Helpers\Cstr;
    use Modules\Core\Models\UnitKerja;

    $unit = $mitra =  "";
    foreach ($data as $id => $section) {
        if ($id == 'informasi-kegiatan') {
            foreach ($section['items'] as $index => $item) {
                if ($item['field'] == 'id_unit_kerja') {
                    $unit = $item['text'];
                } else if ($item['field'] == 'id_mitra') {
                    $data[$id]['items'][$index] = [
                        ...$data[$id]['items'][$index],
                        'text' => $rawData->indukKerjasama->mitra->nama_mitra,
                        'original' => $rawData->indukKerjasama->id_mitra
                    ];
                    $mitra = $rawData->indukKerjasama->mitra->nama_mitra;
                }
            }
        }
    }

@endphp

<x-core::quantum-3.layouts.detail title="Kegiatan" :data="$data">
    @foreach ($data as $id => $section)
    @php
        $attributes = Page::buildAttributes(Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']]);
    @endphp

    @if ($id == 'informasi-penanggung-jawab')
        <x-core::quantum-3.layouts.detail.card {{ $attributes }}>
            <div class="col-md-12">
                <div class="row gx-1 gy-3">
                    @foreach ($rawData->pihak_penanggung_jawab as $item)
                        @php                                
                            if ($item['model_pihak'] == UnitKerja::class) {
                                $pihak = $unit;
                            } else {
                                $pihak = $mitra;
                            }
                            $jenisPihak = PihakPenanggungJawab::MAPPING_MODEL[$item['model_pihak']];
                        @endphp
                        <h5>Pihak Penanggung Jawab Ke {{ $item['pihak_ke'] }}</h5>

                        {{-- Pihak --}}
                        <div class="col-2">
                            <div class="d-flex gap-1 justify-content-between">
                                <span class="text-secondary">{{ __('kerjasama::pihak_penanggung_jawab.id_pihak') }} {{ PihakPenanggungJawab::PIHAK_LABEL[$jenisPihak] }}</span>
                                <span>:&nbsp;</span>
                            </div>
                        </div>
                        <div class="col-10">
                            <span>{{ Cstr::unescapeDeep($pihak) ?? '-' }}</span>
                        </div>

                        <div class="col-12">
                            <x-core::quantum-3.table.data :showNumber="true" :header="$headerPenanggungJawab" :sortable="false" :data="$item->penanggung_jawab"/>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-core::quantum-3.layouts.detail.card>
    @elseif($id == 'informasi-kegiatan')
        <x-core::quantum-3.layouts.detail.card {{ $attributes }}>
            <x-slot:action>
                <x-core::quantum-3.button
                    leadingIcon="printer"
                    variant="primary"
                    onclick="cetakLaporan()"
                >
                    Cetak Laporan
                </x-core::quantum-3.button>
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

            <div class="col-md-12">
                <div class="row gx-1 gy-3">
                    <h5>Dokumen Kegiatan</h5>

                    <div class="col-12">
                        <x-core::quantum-3.table.data 
                            :withSeparator="false" 
                            :header="$headerDokumenKegiatan" 
                            :sortable="false"
                            :data="$rawData->dokumenKegiatan"
                        >
                        <x-slot:empty-element>
                            @php
                                $title = "Belum Ada Data Dokumen Kegiatan";
                                $subtitle = "Silakan menambah dokumen kegiatan melalui button 'Ubah Data'";
                            @endphp
                            <x-core::quantum-3.handler :$title :$subtitle :canCreate="false" />
                        </x-slot:empty-element>
                        </x-core::quantum-3.table.data>
                    </div>
                </div>
            </div>
        </x-core::quantum-3.layouts.detail.card>
    @elseif($id == 'pelaksana-kegiatan')
        <x-core::quantum-3.layouts.detail.card {{ $attributes }}>
            <div class="col-md-12">
                <div class="row gx-1 gy-3">
                    <x-core::quantum-3.table.data 
                        :withSeparator="false" 
                        :header="$headerPelaksanaKegiatan" 
                        :sortable="false"
                        :data="$rawData->pelaksanaKegiatan"
                    >
                        <x-slot:empty-element>
                            @php
                                $title = "Belum Ada Data Pelaksana Kegiatan";
                                $subtitle = "Silakan menambah pelaksana kegiatan melalui button 'Ubah Data'";
                            @endphp
                            <x-core::quantum-3.handler :$title :$subtitle :canCreate="false" />
                        </x-slot:empty-element>
                    </x-core::quantum-3.table.data>
                </div>
            </div>
        </x-core::quantum-3.layouts.detail.card>    
    @endif
    @endforeach

    <div class="d-none">
        <iframe id="laporan-kerjasama" src="{{ route('kerjasama.kegiatan.report', $rawData->id) }}" frameborder="0"></iframe>
    </div>

    @push('scripts')
        <script>
            function cetakLaporan() {
                document.getElementById('laporan-kerjasama').contentWindow.print();
            }
        </script>
    @endpush
</x-core::quantum-3.layouts.detail>
