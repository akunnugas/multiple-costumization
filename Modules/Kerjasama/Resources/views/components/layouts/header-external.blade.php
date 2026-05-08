@props([
    'menu' => [],
    'withFormHeader' => false,
])
@php
    $client = request()->client['nama_klien'];
    // $user = Auth::user();

    // if (!empty($user->kode_modul)) {
    //     $roles = $user->modul[$user->kode_modul]['role'];
    // }
    // <img class="img-fluid w-auto h-100 object-fit-contain"
    //     src="{{ session('token.logo_univ') ?? Page::quantumAsset('images/logo-kampus.png') }}" alt="Example Campus Logo">
    // $urlMenuSiakad = $client['url_siakad_menu'] ?? env('URL_SIAKADV1_MENU', '#');
@endphp


<header {{ $attributes->merge(['class' => 'qn-header z-2 sticky-top']) }}>
    <div class="qn-header-pattern p-md-3 py-3 px-xl-5 bg-primary text-white">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-start">
                <div class="d-flex gap-2 gap-md-3 align-items-center">
        
                    <div class="d-flex flex-column">
                        <span>SIM Kerjasama</span>
                        <h5 class="m-0">{{ $client ?? config('app.name') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
