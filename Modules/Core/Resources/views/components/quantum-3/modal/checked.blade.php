@props([
    'type' => 'delete',
    'id' => null,
    'header' => null,
    'content' => null,
    'footer' => null
])

@php
    $header = "Pilih $resourceTitle Terlebih Dahulu";
    $content = "Silakan centang ".strtolower($resourceTitle)." yang ingin dihapus terlebih dahulu.";
    $arr_button = [
      'close' => [
        'id' => 'button-not-checked',
        'name' => 'Mengerti',
        'class' => 'btn btn-light border',
        'attributes' => 'data-bs-dismiss="modal"',
      ]
    ];
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="modalChekedLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <x-core::quantum-3.modal.container.header-modal :id="$id" :header="$header" />
        <x-core::quantum-3.modal.container.content-modal :content="$content" />
        <x-core::quantum-3.modal.container.footer-modal :footer="$footer" :arr_button="$arr_button" />

      </div>
    </div>
  </div>
