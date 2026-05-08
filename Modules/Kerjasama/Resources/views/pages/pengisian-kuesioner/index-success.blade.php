@php
    use Modules\Core\Helpers\Page;
@endphp

<x-kerjasama::layouts.main-outer-external :withContainer="false">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="card text-center" style="padding: 50px; padding-bottom: 40px;">  
            <div class="mb-2">
                 <x-core::quantum-3.icon icon="check-circle-solid" class="text-success" style="color: #0E9384; font-size: 80px;" />
            </div>
            <h4 class="fw-bold text-dark mb-1">Terima kasih!</h4>
            <p class="text-muted mb-1">Jawaban Anda telah tersimpan.</p>
        </div>
    </div>
</x-kerjasama::layouts.main-outer-external>
