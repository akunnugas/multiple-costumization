@php
   
    $unit = $mitra = "";

    foreach ($data as $id => $section) {
        if ($id == 'informasi-kerjasama') {
            foreach ($section['items'] as $item) {
                if ($item['field'] == 'id_unit_kerja') {
                    $unit = $item['text'];
                } else if ($item['field'] == 'id_mitra') {
                    $mitra = $item['text'];
                }
            }
        }
    }
    $rawData->pertanyaan = collect($rawData->pertanyaan)->sortBy('nomor')->values()->all();
@endphp

<x-core::quantum-3.layouts.detail :data="$data" title="Evaluasi Kuesioner">

    @foreach ($data as $id => $section)

        @php
            $attributes = Page::buildAttributes(Arr::only($section, ['title', 'subtitle', 'icon', 'page_conf']) + ['data' => $section['items']]);
        @endphp


        <x-core::quantum-3.layouts.detail.card {{ $attributes }}>
            <x-slot:action>

                @if ($id == 'informasi-kuesioner')
                    <x-core::quantum-3.button leadingIcon="plus" variant="primary"
                        href="{{ route('kerjasama.evaluasi-kuesioner.edit', $rawData->id) }}?evaluasi_kuesioner={{ $rawData->id }}&backUrl={{ url()->current() }}">
                        Tambah Data Soal
                    </x-core::quantum-3.button>

                @endif

                @if ($id == 'informasi-kerjasama')
                    <x-core::quantum-3.button variant="primary" leadingIcon="link-solid"
                        onclick="copyLink('{{ route('kerjasama.kuesioner.view', $rawData->uuid) }}')">
                        Salin Link
                    </x-core::quantum-3.button>
                
                    @push('scripts')
                    <script>
                        function copyLink(url) {
                            navigator.clipboard.writeText(url).then(function() {
                                // Default browser alert or custom toast if available
                                alert('Link kuesioner berhasil disalin!'); 
                            }, function(err) {
                                console.error('Gagal menyalin link: ', err);
                            });
                        }
                    </script>
                    @endpush
                @endif
                @if (!empty($section['edit_url']))
                    <x-core::quantum-3.button href="{{ $section['edit_url'] }}" variant="light"
                        leading-icon="{{ $section['edit_icon'] ?? 'edit-02' }}">
                        {{ $section['edit_label'] ?? 'Ubah Data' }}
                    </x-core::quantum-3.button>
                @endif
            </x-slot:action>

            @if ($id == 'informasi-kuesioner')

                @foreach ($rawData->pertanyaan as $pertanyaan)


                    <x-core::quantum-3.card-soal :number="$pertanyaan->nomor" :question="$pertanyaan->pertanyaan" :evaluasi="$rawData->id" :pertanyaanId="$pertanyaan->id"
                        :options="($pertanyaan->opsiJawaban && $pertanyaan->opsiJawaban->count() > 0) ? $pertanyaan->opsiJawaban->pluck('jawaban')->toArray() : []" :required="$pertanyaan->apakah_wajib"
                        :type="$pertanyaan->tipe" :scale="$pertanyaan->rating"  />
                @endforeach

                @elseif($id == 'peserta')

                @if ($rawData->peserta && $rawData->peserta->count() > 0)
                    <x-core::quantum-3.alert variant="info" class="mb-3" :dismissable="false">
                        Jumlah responden dapat berbeda karena perubahan pertanyaan setelah sebagian responden mengisi kuesioner
                    </x-core::quantum-3.alert>
                    <x-core::quantum-3.card-rekap-jawaban :peserta="$rawData->peserta" :pertanyaan="$rawData->pertanyaan" />
                @else
                    <div class="alert alert-info">
                        Belum ada peserta yang mengisi kuesioner ini.
                    </div>
                @endif
            @else
                @php

                    $data = $section['items'];
                    if (count($data) >= 8) {
                        $half = ceil(count($data) / 2);
                        $dataChunks = array_chunk($data, $half);
                    } else {
                        $dataChunks = [$data];
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

            @endif


        </x-core::quantum-3.layouts.detail.card>
    @endforeach
</x-core::quantum-3.layouts.detail>