@props([
    'container' => true
])

@if($container)
<div class="container-fluid mt-auto bg-white border-top">
@endif
    <footer class="d-flex flex-wrap justify-content-center justify-content-md-between align-items-center py-1 py-md-2">
        <div class="col-md-6 d-flex align-items-center">
            <a href="#" class="d-none d-md-block me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
                <img src="{{ url('images/logo-sevima-platform.png') }}" class="w-auto" height="18"
                    alt="Logo SEVIMA">
            </a>
        </div>
        <span class="mb-0 fs-7 text-body-tertiary">&copy; 2005-{{ date('Y') }}. All Rights Reserved</span>
    </footer>
@if($container)
</div>
@endif


{{-- <div class="container-fluid mt-auto bg-white border-top">
    <footer
        class="d-flex flex-wrap justify-content-center justify-content-md-between align-items-center py-1 py-md-2">
        <div class="col-md-6 d-flex align-items-center">
            <a href="#" class="d-none d-md-block me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
                <img src="../../node_modules/@quantum/web/assets/logos/sevima-default.webp" class="w-auto" height="18" alt="Logo SEVIMA">
            </a>
        </div>
        <span class="mb-0 fs-7 text-body-tertiary">&copy; 2005-2023 SEVIMA. All Rights Reserved</span>
    </footer>
</div> --}}
