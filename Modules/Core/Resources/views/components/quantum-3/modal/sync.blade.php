@props([
    'id',
    'resourceTitle' => null,
    'syncMessage' => null,
    'syncTitle' => null,
    'content' => null,
    'footer' => null,
])

@php
    $header = $syncTitle ?? "Sinkronisasi {$resourceTitle}";
    $message = $syncMessage ?? "Apakah Anda yakin mensinkronisasi data " . strtolower($resourceTitle ?? '');
    $arr_button = [
        'close' => [
            'id' => 'sync-cancel-button',
            'name' => 'Batal',
            'class' => 'btn btn-light border',
            'attributes' => 'data-bs-dismiss="modal"',
        ],
        'submit' => [
            'id' => 'btn_sync_checked',
            'name' => 'Sinkronisasi',
            'class' => 'btn btn-primary',
        ],
    ];

     $attributes = $attributes->merge([
        'title' => $syncTitle ?? 'Sinkronisasi ' . ($resourceTitle ?? 'Data'),
        'formMethod' => 'POST',
        'variant' => 'primary',
    ]);
@endphp

<div class="modal fade" {{ $attributes }} id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <x-core::quantum-3.modal.container.header-modal :id="$id" :header="$header" />
            <x-core::quantum-3.modal.container.content-modal :content="$content ?? $message" />
            <x-core::quantum-3.modal.container.footer-modal :footer="$footer" :arr_button="$arr_button" />
        </div>
    </div>
</div>