@props([
    'menu' => [],
    'withFormHeader' => false,
])
@php
    $client = request()->client;
    $user = Auth::user();

    if (!empty($user->kode_modul)) {
        $roles = $user->modul[$user->kode_modul]['role'];
    }
@endphp


<header {{ $attributes->merge(['class' => 'qn-header z-2 sticky-top']) }}>
    <div class="qn-header-pattern p-md-3 py-3 px-xl-5 bg-primary text-white">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-start">
                <div class="d-flex gap-2 gap-md-3 align-items-center">
                    <button
                        class="btn btn-icon btn-lg btn-light rounded-1 d-block d-lg-none bg-transparent text-white"
                        type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="sym sym-menu-03"></i>
                    </button>
                    <a href="{{ Page::homeURL() }}"
                        class="qn-identity d-flex align-items-center link-body-emphasis text-decoration-none rounded-3 bg-white">
                        <img class="img-fluid w-auto h-100 object-fit-contain"
                            src="{{ session('token.logo_univ') ?? Page::quantumAsset('images/logo-kampus.png') }}" alt="Example Campus Logo">
                    </a>
                    <div class="d-flex flex-column">
                        <span>SIM {{ $user->nama_modul }}</span>
                        <h5 class="m-0">{{ $client['nama_klien'] ?? config('app.name') }}</h5>
                    </div>
                </div>
                <div class="ms-auto d-flex align-items-center gap-1">
                    <div class="d-none d-md-flex align-items-center gap-1">
                        <a href="{{ $urlMenuSiakad ?? '#' }}" class="btn btn-light p-2 py-1 bg-transparent text-white border-0" aria-label="Pindah Modul">
                            <i class="sym sym-dots-grid-solid"></i>
                            <span class="d-none d-lg-inline-block ms-2">Modul</span>
                        </a>
                    </div>
                    <hr class="d-none d-md-block vr mx-2">
                    <!-- [START REDUNDANT CONTENT] Header Avatar Group (fmt. 1) -->
                    <div class="dropdown d-none d-md-block rounded-4 bg-white text-black p-1">
                        <a href="#"
                            class="d-flex gap-1 align-items-center link-body-emphasis text-decoration-none dropdown-toggle pe-1"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-center rounded-4" style="width: 32px; height: 32px; line-height: 32px;">
                                <i class="sym sym-user-circle-solid fs-3"></i>
                            </div>
                            <span class="qn-avatar-name d-none d-lg-block text-truncate">{{ $user->nama_user }}</span>
                        </a>
                        <ul class="dropdown-menu pt-0 text-small" style="width: 296px;">
                            <li>
                                <div class="d-flex flex-column gap-2 p-3 py-4 pb-3 text-nowrap">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-center rounded-4" style="width: 32px; height: 32px; line-height: 32px;">
                                            <i class="sym sym-user-circle-solid fs-3"></i>
                                        </div>
                                        <div class="d-block">
                                            <h6 class="mb-1">{{ $user->nama_user }}</h6>
                                            <span class="text-muted">{{ $user->nama_role }}</span>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <button class="dropdown-item d-flex flex-nowrap justify-content-between" data-bs-toggle="modal" data-bs-target="#modal-switch-role">
                                    Ganti Role
                                    <i class="sym sym-refresh-ccw-02"></i>
                                </button>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ url(route('logout')) }}">
                                    <i class="sym sym-arrow-left-solid me-2"></i>
                                    Keluar
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-core::quantum-3.layouts.outer.header.menu :data="$menu" :$withFormHeader />
</header>
@if (!empty($roles))
    <x-core::quantum-3.modal.switch-role id="modal-switch-role" :roles="$roles" :module="$user?->kode_modul" :selectedRole="$user?->kode_role"
        :selectedOrganization="$user?->id_unit" :action="route('switch-role')" />
@endif
