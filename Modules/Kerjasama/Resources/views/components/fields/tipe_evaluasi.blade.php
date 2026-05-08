@php
    use Modules\Kerjasama\Models\Evaluasi;
@endphp
<div class="w-100 d-flex justify-content-center">
    <span class="badge bg-secondary">{{ Evaluasi::getTipeEvaluasiLabelAttribute($data['tipe_evaluasi']) }}</span>
</div>
    