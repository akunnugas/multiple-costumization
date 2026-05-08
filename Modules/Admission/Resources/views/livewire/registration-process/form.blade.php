<x-admission::layouts.registration-process
    :$title :$subtitle :$parentNav :$stepPercentage :$period :$alert :$record
>
    <form method="POST" wire:submit="doProcessPreviewData">
        @csrf
        @method('POST')
        <div class="registration-form-card-body util_p-20">
            <h4>Lengkapi Datamu Sekarang</h4>
            <p>Jangan sampai kehabisan kuota! Sedikit lagi kamu akan terdaftar di perguruan tinggi impianmu.</p>

            @foreach ($data as $field)
                <div class="title-section">
                    <h4 class="util_m-0">{{ $field['title'] }}</h4>
                    <hr class="util_flex-grow-1">
                </div>
                <div class="grid">
                    @foreach ($field['items'] as $item)
                        @php
                            $item['name'] ??= $item['field'];
                            unset($item['field']);

                            // set default placeholder
                            $item['placeholder'] ??= 'Isi ' . $item['label'] . ' Anda';

                            $attributes = \Modules\Core\Helpers\Page::buildAttributes($item);
                        @endphp
                        <div class="col-12 col-md-6">
                            <x-core::controls.form {{ $attributes }} />
                            @if(!empty($item['name']) && $item['name'] == 'school_id')
                                @php
                                    // TODO: belum utk interaksi modal dan proses simpannya
                                @endphp
                                <small class="text-warning text-help">
                                    Jika data tidak ditemukan, silakan
                                    <span id="add-school" class="link" style="cursor:pointer"> +Tambah Sekolah</span>
                                </small>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="line-bold util_d-block"></div>
        <div class="registration-form-card-footer util_p-20">
            <div></div>
            <x-core::button variant="primary" wire:click="doProcessPreviewData" id="next-step-registration"
                            @click="scrollTo({top: 0, behavior: 'smooth'})" disabled>
                Lanjutkan Mendaftar <x-core::icon type="arrow-long-right" />
            </x-core::button>
        </div>
    </form>

    @pushonce('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('element.init', ({el}) => {
                    customHookChoices(el);
                });
                Livewire.hook('morph.updating', ({el}) => {
                    customHookChoices(el);
                });

                Livewire.on('disable-city', () => {
                    // TODO: belum bisa disable
                    // if (cityChoices === undefined) {
                    //     cityChoices = new Choices(cityElm, {
                    //         allowHTML: false,
                    //         shouldSort: false,
                    //         searchEnabled: true,
                    //         searchResultLimit: 7,
                    //     });
                    // }
                    disabledChoices(cityChoices, emptyCityValue);
                });
            });
        </script>
    @endpushonce
</x-admission::layouts.registration-process>
