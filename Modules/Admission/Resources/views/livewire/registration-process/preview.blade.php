<x-admission::layouts.registration-process
    :$title :$subtitle :$parentNav :$stepPercentage :$period :$alert :$record
>
    <div class="registration-form-card-body util_p-20">
        @foreach ($data as $field)
            <div class="title-section">
                <h4 class="util_m-0">{{ $field['title'] }}</h4>
                <hr class="util_flex-grow-1">
            </div>
            <div class="grid">
                @foreach ($field['items'] as $item)
                    <div class="col-md-2 col-4 util_text-secondary">
                        {{ $item['label'] }}
                    </div>
                    <div class="col-md-4 col-8 util_text-default">
                        {{ $item['text'] ?? '-' }}
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    <div class="line-bold util_d-block"></div>
    <div class="registration-form-card-footer util_p-20">
        <div>
            <input type="checkbox" id="is_confirmed" autocomplete="off">
            <label for="is_confirmed" class="util_text-default">
                Saya menyetujui bahwa data yang telah dimasukkan adalah Benar dan dapat dipertanggungjawabkan.
            </label>
        </div>
        <div>
            <x-core::button variant="outline" wire:click="doChangeData">
                Ubah Data
            </x-core::button>
            <x-core::button variant="primary" id="btn-next-step" wire:click="doSubmitPreview" disabled>
                Lanjutkan Mendaftar <x-core::icon type="arrow-long-right" />
            </x-core::button>
        </div>
    </div>

    @pushonce('headVendor')
        <link rel="stylesheet"
              href="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/styles/choices.min.css') }}">
        <script src="{{ Page::quantumAsset('js/vendors/choices.js-10.2.0/public/assets/scripts/choices.min.js') }}"></script>
    @endpushonce
</x-admission::layouts.registration-process>
