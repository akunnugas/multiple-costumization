@props([
    'id' => null,
    'header' => null,
    'content' => null,
    'footer' => null
])

@php
    $header = "Hapus $resourceTitle";
    $content = "Apakah Anda yakin ingin menghapus data $resourceTitle? Karena data yang telah dihapus tidak dapat dikembalikan lagi.";
    $arr_button = [
        'close' => [
            'id' => 'delete-many-cancel-button',
            'name' => 'Batal',
            'class' => 'btn btn-light border',
            'attributes' => 'data-bs-dismiss="modal"',
        ],
        'submit' => [
            'id' => 'delete-many-button',
            'name' => 'Hapus',
            'class' => 'btn btn-danger',
        ],
    ];
    
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}"  aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <x-core::quantum-3.modal.container.header-modal :id="$id" :header="$header" />
        <x-core::quantum-3.modal.container.content-modal :content="$content" />
        <x-core::quantum-3.modal.container.footer-modal :footer="$footer" :arr_button="$arr_button" />
    </div>
  </div>
</div>
