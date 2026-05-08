@props([
    'prefixTitle' => null,
])

<div id="doc-preview-modal" class="modal">
    <div class="modal__overlay" data-dismiss="modal"></div>
    <div class="grid"
        style="position: fixed; top:0; display: flex; padding: 12px 24px; z-index: 1000; 
        justify-content: space-between; align-items: center; background: #222; left: 0;">
        <button type="button" class="close btn btn_ghost btn_xs"
            style="color: #fff !important; background: transparent !important; outline: none;">
            <span class="icon icon-arrow-left" style="color: #fff;"></span>
            Kembali
        </button>
        <div style="display: flex; align-items: center; color: #fff; gap: 8px">
            <span class="icon icon-eye"></span>
            <h2 id="modal_header-title" class="header__title" style="font-size: 14px; color: #fff">
                {{ $prefixTitle }}<span id="preview-title"></span>
            </h2>
        </div>
        <x-core::button id="dl-template" leading-icon="arrow-down-tray" variant="primary" size="xs">
            <span class="btn__text">Download Dokumen</span>
        </x-core::button>
    </div>
    <div class="modal__wrapper" style="width: 50%; background: transparent; box-shadow: none;">
        <div class="modal__body" style="width: 100%">
            <input id="preview-doc-link" type="hidden" />
            <div id="doc-viewer"></div>
        </div>
    </div>
</div>

@pushOnce('scripts')
    <script type="text/javascript" src="{{ asset('js/pdf.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/doc-viewer.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jszip.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/docx-preview.min.js') }}"></script>
    <script>
        document.querySelectorAll("button[data-trigger-modal]").forEach(element => {
            element.addEventListener('click', function() {
                const linkDoc = element.getAttribute('data-link');
                const nameDoc = element.getAttribute('data-title');
                const modal = document.querySelector('#doc-preview-modal');
                // check if has class is-visible
                if (modal.classList.contains('is-visible')) {
                    modal.classList.remove('is-visible');
                } else {
                    document.querySelector('#preview-doc-link').value = linkDoc;
                    document.querySelector('#preview-title').innerHTML = nameDoc;
                    modal.classList.add('is-visible');
                    const viewer = document.querySelector("#doc-viewer");
                    viewer.innerHTML = "";
                    viewer.removeAttribute("style");

                    const container = document.getElementById('doc-viewer');
                    const docUrl = linkDoc;
                    renderPDF(container, docUrl, 1);
                }
            });
        });

        document.querySelector('#doc-preview-modal .close').addEventListener('click', function() {
            const modal = document.querySelector('#doc-preview-modal');
            // check if has class is-visible
            if (modal.classList.contains('is-visible')) {
                modal.classList.remove('is-visible');
            } else {
                modal.classList.add('is-visible');
            }
        });

        // dl-template
        document.querySelector('#dl-template').addEventListener('click', function() {
            // download template preview-doc-link
            const linkDoc = document.querySelector('#preview-doc-link').value;
            var link = document.createElement('a');
            link.setAttribute('download', true);
            link.href = linkDoc;
            document.body.appendChild(link);
            link.click();
            link.remove();
        });
    </script>
@endPushOnce
