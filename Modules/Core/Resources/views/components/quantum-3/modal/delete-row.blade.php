@props([
    'type' => 'delete',
    'id' => null,
    'header' => null,
    'content' => null,
    'footer' => null,
    'action' => null,
    'method' => 'DELETE',
    'withUpload' => true,
    'resourceTitle' => null,
])

@php
    $header = $header ?? "Hapus $resourceTitle";
    $content = $content ?? "Apakah Anda yakin ingin menghapus data $resourceTitle? Karena data yang telah dihapus tidak dapat dikembalikan lagi.";
    $arr_button = [
        'close' => [
            'id' => 'delete-cancel-button',
            'name' => 'Batal',
            'class' => 'btn btn-light border',
            'attributes' => 'data-bs-dismiss="modal"',
        ],
        'submit' => [
            'id' => 'delete-button',
            'name' => 'Hapus',
            'class' => 'btn btn-danger',
        ],
    ];
@endphp


<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}_Label" aria-hidden="true" >
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Formulir Penghapusan -->
            <form method="{{ $method == 'GET' ? 'GET' : 'POST' }}" 
                id="{{ $id }}_form" 
                @if (!empty($action)) action="{{ $action }}" @endif
                @if (!empty($withUpload)) enctype="multipart/form-data" @endif 
                {{ $attributes }}>
                
                @csrf
                @if (!in_array($method, ['GET', 'POST']))
                @method($method)
                @endif

                <x-core::quantum-3.modal.container.header-modal :id="$id" :header="$header" />
                <x-core::quantum-3.modal.container.content-modal :content="$content" />
                <x-core::quantum-3.modal.container.footer-modal :footer="$footer" :arr_button="$arr_button" />
            </form>
        </div>
    </div>
</div>
