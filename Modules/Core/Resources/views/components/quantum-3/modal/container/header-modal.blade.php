@props([
    'id' => null,
    'header' => 'header',
])

<div class="modal-header">
    <div class="d-flex">
        {{-- <div class="container-icon">
            <i class="float-end sym sym-info-default"></i>
        </div> --}}
        <h1 class="modal-title fs-5" id="{{ $id }}">{{ $header }}</h1>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  </div>

  {{-- <style>
    .container-icon {
        width: 40px;
        height: 40px;
        background-color: #0D6EFD;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .container-icon:hover {
        background-color: #0b5ed7;
    }

    .container-icon i {
        color: white;
        font-size: 1rem;
        margin-left: 2px;
    }
</style> --}}