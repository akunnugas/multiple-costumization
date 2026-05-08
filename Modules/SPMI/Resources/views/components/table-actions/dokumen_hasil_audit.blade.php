@php
    use Illuminate\Support\Facades\Storage;
    use Modules\SPMI\Services\HasilAkhirAuditManagementService;

    $isCreated = $data['is_created'];
    $method = 'POST';

    $permission = request()->permission;
    $canCreate = $permission['post'] ?? false;
    $canUpdate = $permission['put'] ?? false;

    $organizationId = $data['id_unit'];

    if (empty($isCreated)) {
        $action = route('spmi.dokumen-hasil-audit.store');
    } else {
        $id = $data['id'];
        $method = 'PUT';
        $action = route('spmi.dokumen-hasil-audit.update', [$id]);
    }

    if (!empty($data['alamat_versi_terbaru'])) {
        $documentUrl = Storage::temporaryUrl($data['alamat_versi_terbaru'], now()->addMinutes(60));
    }

    $reportUrl = route('spmi.hasil-akhir-audit.laporan', [$data['id_hasil_akhir_audit']]);

    $cannotAction = empty($documentUrl) && (!$canCreate && !$canUpdate);
@endphp

<div class="dropdown">
    <button type="button" class="btn btn_primary btn_xs" data-toggle="dropdown" style="" aria-expanded="false"
        @if ($cannotAction) disabled @endif>
        <span class="btn__text">Atur Dokumen</span>
        <span class="icon icon-chevron-down-mini"></span>
    </button>
    <div class="dropdown__box">
        <ul class="dropdown__list">
            @if (!empty($documentUrl))
                <li class="dropdown__item">
                    <button type="button" data-trigger-modal="preview-template-kebijakan"
                        data-link="{{ $documentUrl }}" data-title="{{ $data['nama_dokumen'] }}">
                        <span class="icon icon-eye"></span>
                        Lihat Dokumen
                    </button>
                </li>
            @endif

            @if ($canCreate || $canUpdate)
                <li class="dropdown__item">
                    <button type="button" class="btn-upload" data-organization="{{ $organizationId ?? null }}"
                        data-id="{{ $id ?? null }}" data-toggle="modal" data-method="{{ $method }}"
                        data-action="{{ $action }}" data-target="#upload-document"
                        data-auditPeriod="{{ $data['id_audit_periode'] }}"
                        data-jadwalAudit="{{ $data['id_jadwal_audit'] ?? null }}">
                        <span class="icon icon-arrow-up-tray"></span>
                        Upload Dokumen
                    </button>
                </li>
                <li class="dropdown__item">
                    <button type="button" onclick="printExternal('{{ $reportUrl }}')">
                        <span class="icon icon-printer"></span>
                        Cetak Dokumen
                    </button>
                </li>
            @endif
        </ul>
    </div>
</div>
