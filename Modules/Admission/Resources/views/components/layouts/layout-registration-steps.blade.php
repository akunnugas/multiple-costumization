@props([
    'title' => null,
    'subtitle' => null,
])

<div>
    {{-- Breadcrumb --}}
    <x-admission::breadcrumb />

    {{-- Step Administration --}}
    <div class="step-registration">
        <div class="container">
            <div class="grid">
                <div class="col-12 col-md-3">
                    {{-- Side Menu --}}
                    <x-admission::step-side-menu />
                </div>

                <div class="col-12 col-md-9">
                    <div class="content-body card">
                        <div class="card__body">
                            @if(!empty($title) && !empty($subtitle))
                                <div class="grid">
                                    <div class="content-header col-12" translate="no">
                                        <h2 class="main-header">{{ $title }}</h2>
                                        <p class="sub-header">{{ $subtitle }}.</p>
                                    </div>
                                </div>
                            @endif
                            <div class="grid">
                                <div class="col-12">
                                    {{-- Header Informasi Pendaftar --}}
                                    <x-admission::header.registrant-information />

                                    {{-- Main Content --}}
                                    <div id="main-content-registration-step">
                                        {{ $slot }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
