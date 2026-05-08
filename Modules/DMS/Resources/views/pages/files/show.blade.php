@php
    use Modules\DMS\Services\DokumenManagementService;
    use Modules\DMS\Models\Dokumen;

    $title = "Preview \"$dokumen->nama_dokumen\"";
    $backUrl = route('dms.folder.show', $dokumen->kode_folder);

    $temporaryDocUrl = $dokumen->lastVersionTemporaryUrl();
@endphp
<x-dms::layouts.dashboard :$menu :$title :backUrl="$backUrl">
    <x-core::layouts.html.alert />
    <style>
        .pdf-page {
            page-break-before: always;
            width: auto;
            display: block;
            margin: 0 auto;
        }

        .preview {
            background: var(--qn-neutral-300);
            padding: 1rem;
            overflow-y: auto;
            height: 70svh;
        }

        .preview-container {
            width: 100%;
            max-width: 45rem;
            margin: 0 auto;
        }
    </style>

    <div class="preview">
        <div class="preview-container">
            <div id="doc-viewer" style="display: flex; flex-direction: column; align-items: center; width:100%">
                @if(in_array($dokumen->extension_versi_terbaru, Dokumen::TYPE_IMAGE))
                    <img src="{{ $temporaryDocUrl }}" alt="{{ $dokumen->nama_dokumen }}" style="max-width: 100%; height: auto;">
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="text/javascript" src="{{ asset('js/pdf.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/doc-viewer.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/jszip.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/docx-preview.min.js') }}"></script>
        <script>
            const container = Dokumen.getElementById('doc-viewer');
            const docUrl = `{!! $temporaryDocUrl !!}`;

            window.addEventListener("DOMContentLoaded", () => {
                @if ($dokumen->extension_versi_terbaru == 'pdf')
                renderPDF(container, docUrl, 1);
                @elseif ($dokumen->extension_versi_terbaru == 'docx')
                renderDOCX(container, docUrl);
                @endif
            })
        </script>
    @endpush

</x-dms::layouts.dashboard>
