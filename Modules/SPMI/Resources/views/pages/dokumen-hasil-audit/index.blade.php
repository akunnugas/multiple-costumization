<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="false" :canDelete="false" :showNumber="$showNumber ?? false" :emptyState="$emptyState ?? []">
    @push('head')
        @vite('resources/scss/custom-utils.scss')
    @endpush

    <x-slot:tableHeader>
        <div class="alert alert alert_helper util_mb-16">
            <div class="alert__content">
                <h4 class="alert__heading">Unggah dan Kelola Berita Acara AMI</h4>
                <p>
                    Pastikan setiap unit kerja telah memiliki berkas berita acara hasil AMI. Unduh template berita acara, mintakan tanda tangan pimpinan, lalu unggah kembali dokumen yang telah ditandatangani.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
    </x-slot:tableHeader>
    <x-slot:outer>
        <x-spmi::detail.document-modal prefixTitle="Hasil - " />
    </x-slot:outer>
    @pushOnce('scripts')
        <script type="text/javascript">
            document.querySelector('body').addEventListener('click', function(event) {
                if (event.target.closest('.btn-upload')) {
                    const clickedBtn = event.target.closest('.btn-upload');

                    const id = clickedBtn.getAttribute('data-id');
                    const organizationId = clickedBtn.getAttribute('data-organization');
                    const auditPeriodId = clickedBtn.getAttribute('data-auditPeriod');

                    document.querySelector('#upload-document input[name="id_unit"]').setAttribute(
                        'value',
                        organizationId);
                    document.querySelector('#upload-document input[name="id_audit_periode"]').setAttribute(
                        'value',
                        auditPeriodId);

                    const jadwalAuditId = clickedBtn.getAttribute('data-jadwalAudit');
                    document.querySelector('#upload-document input[name="id_jadwal_audit"]').setAttribute(
                        'value',
                        jadwalAuditId);

                    document.querySelector('#upload-document input[name="id_data"]').setAttribute('value',
                        id);
                }
            });
        </script>
    @endpushOnce
</x-core::layouts.list>

<div id="upload-document" class="modal">
    <div class="modal__overlay" data-dismiss="modal"></div>
    <div class="modal__wrapper">
        <div class="modal__header">
            <div class="modal__header-wrapper">
                <h3 class="modal__title">Upload Berita Acara</h3>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="modal"></span>
        </div>
        <x-core::form :action="route('spmi.dokumen-hasil-audit.store')" method="POST">
            {{-- Hidden input --}}
            <input type="hidden" name="id_data">
            <input type="hidden" name="id_unit">
            <input type="hidden" name="id_audit_periode">
            <input type="hidden" name="id_jadwal_audit">

            <div class="modal__body">
                <div class="modal__content-upload">
                    <div class="form-control">
                        <div class="upload-draggable">
                            <div class="upload-draggable__box">
                                <input type="file" class="upload-draggable__file-input" name="id_dokumen"
                                    id="" accept=".pdf">
                                <label class="upload-draggable__icon"><span
                                        class="icon icon-cloud-arrow-up"></span></label>
                                <h2 class="upload-draggable__title">Klik untuk pilih file</h2>
                                <p class="upload-draggable__subtitle">atau seret file ke sini</p>
                                <p class="upload-draggable__support">PDF (Max. 5MB)</p>
                            </div>
                            <div class="upload-draggable__success">
                                Berhasil
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal__footer">
                <div class="grid cols-1 cols-sm-2">
                    <button class="btn btn_outline" type="button" data-dismiss="modal">
                        Batal
                    </button>
                    <button class="btn btn_primary" type="submit">Konfirmasi</button>
                </div>
            </div>
        </x-core::form>
    </div>
</div>
