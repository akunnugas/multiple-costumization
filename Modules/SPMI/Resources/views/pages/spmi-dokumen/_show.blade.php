@php
    use Modules\DMS\Services\DokumenManagementService;

    // title
    $title = 'Detail Dokumen ' . $qualityType->nama_spmi_jenis_dokumen;
    $subtitle ??= null;

    // Mapping kolom yang tampil
    $showColumn = ['kode_spmi_dokumen', 'nama_spmi_dokumen', 'versi', 'apakah_aktif', 'tanggal_awal_berlaku', 'tanggal_akhir_berlaku', 'deskripsi'];
    $items = $data[0]['items'];
    $documentId = null;
    $typeId = null;

    // Jika field ada di showColumn, maka tampilkan
    $items = array_map(function ($item) use ($items, &$documentId, &$typeId) {
        $item = array_filter(
            $items,
            function ($value, $key) use ($item, &$documentId, &$typeId) {
                // Jika dokumen, maka ambil id dokumen
                if ($value['field'] == 'id_dokumen') {
                    $documentId = $value['original'];
                }

                // Jika tipe dokumen, maka ambil id tipe dokumen
                if ($value['field'] == 'id_jenis') {
                    $typeId = $value['original'];
                }

                return $value['field'] == $item;
            },
            ARRAY_FILTER_USE_BOTH,
        );
        $item = array_values($item);

        if (!empty($item)) {
            return $item[0];
        }
    }, $showColumn);
    $items = array_values(array_filter($items));

    $document = (new DokumenManagementService())->show($documentId);
    $temporaryDocUrl = $document->lastVersionTemporaryUrl();
@endphp
<x-core::layouts.main :$menu>
    <x-core::layouts.html.alert />
    <style>
        .pdf-page {
            page-break-before: always;
            width: auto;
            display: block;
            margin: 0 auto;
        }
    </style>
    <div class="grid" style="height: 80vh; column-gap: 0;">
        <div class="col-12 col-sm-9 col-md-6">
            @if (!empty($header))
                <x-core::layouts.detail.header :data="$header" />
            @endif
            <div class="card card_details-default" style="border-radius: 0px; padding: 24px 28px;">
                <div class="card__header">
                    <div class="card__header-left">
                        <div class="card__header-block">
                            <h2 class="header__title" style="font-size: 16px">{{ $title }}</h2>
                        </div>
                    </div>
                    <div class="card__header-right">
                        <x-core::button href="{{ $data[0]['edit_url'] }}" variant="link"
                            leading-icon="pencil-square-solid">
                            Ubah Data
                        </x-core::button>
                    </div>
                </div>
                <div class="card__body">
                    <div class="grid" style="row-gap: 10px">
                        @foreach ($items as $item)
                            @php
                                $label = $item['label'] ?? null;
                                $text = $item['text'] ?? null;
                                if (empty($label) && !empty($item['field'])) {
                                    $label = Page::defineLabelByField($item['field']);
                                }

                                // Jika format tanggal, maka ubah format
                                if (\Carbon\Carbon::hasFormat($text, 'Y-m-d')) {
                                    $text = \Carbon\Carbon::parse($text)->translatedFormat('d F Y');
                                }

                                // Jika status ubah text
                                if ($item['field'] == 'apakah_aktif') {
                                    $text = $item['text'] == 'Tidak' ? 'Tidak Berlaku' : 'Berlaku';
                                }

                            @endphp
                            @if (in_array($item['field'], $showColumn))
                                <div class="col-12 col-lg-12"
                                    style="border-bottom: 1px solid #EEF2F6; padding-bottom: 5px">
                                    <div class="row-data">
                                        <label class="row-data__name" style="width: 40%">{{ $label }}</label>
                                        <span class="row-data__value" style="width: 60%">
                                            <span class="row-data__colon">:&nbsp;</span>
                                            {{ $text }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-9 col-md-6" style="background: #E3E8EF; height: 100%; overflow-y: auto">
            <div style="padding: 24px 3rem; width:100%">
                <div style="display: flex; align-items: center; gap: 4px;">
                    <h5 class="main__title" style="font-size: 12px;">Dokumen Mutu</h5>
                </div>
                <div id="doc-viewer" style="display: flex; flex-direction: column; align-items: center; width:100%; padding-top: 10px;">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="text/javascript" src="{{ asset('js/pdf.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/doc-viewer.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/jszip.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/docx-preview.min.js') }}"></script>
        <script>
            const container = document.getElementById('doc-viewer');
            const docUrl = `{!! $temporaryDocUrl !!}`;

            window.addEventListener("DOMContentLoaded", () => {
                @if ($document->extension_versi_terbaru == 'pdf')
                    renderPDF(container, docUrl, 1);
                @elseif ($document->extension_versi_terbaru == 'docx')
                    renderDOCX(container, docUrl);
                @endif
            })
        </script>
    @endpush

</x-core::layouts.main>
