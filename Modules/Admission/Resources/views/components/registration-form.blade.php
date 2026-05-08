@props([
    'data' => [],
    'alert' => null
])
<div id="mini-registration-form">
    <x-core::button class="util_border-radius-bottom-0" variant="primary" href="#" trailingIcon="document-arrow-down">
        Unduh Brosur
    </x-core::button>
    <x-core::form wire:submit="save">
        <div class="card util_border-radius-top-0 util_border-top-none">
            <div class="card__body">
                <h2 class="card__title util_p-0 util_mb-10">Mari bergabung bersama kami</h2>
                <p class="card__subtitle">Jangan sampai kehabisan kuota! Segera isi form dan kamu akan terdaftar di
                    perguruan
                    tinggi
                    impianmu.</p>
            </div>
            <hr>
            <div class="card__body">
                <x-core::layouts.html.alert :data="$alert" class="util_mb-20"/>
                <div class="grid">
                    @foreach ($data as $item)
                        @php
                            $item['label'] = __('admission::home_single.' . $item['field']) ?? $item['field'];
                            $item['name'] ??= $item['field'];
                            unset($item['field']);

                            $attributes = new \Illuminate\View\ComponentAttributeBag($item);
                        @endphp
                        <div class="col-12">
                            <x-core::controls.form {{ $attributes }} />
                        </div>
                    @endforeach
                </div>
                {{-- <p class="util_mt-10">Syarat & ketentuan berlaku</p> --}}
            </div>
            <div class="card__footer">
                <x-core::button variant="primary" type="submit" style="width:100%">
                    Daftar Sekarang
                </x-core::button>
            </div>
        </div>
    </x-core::form>
</div>
