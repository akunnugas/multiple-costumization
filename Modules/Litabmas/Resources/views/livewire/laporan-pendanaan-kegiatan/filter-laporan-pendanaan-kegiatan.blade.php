<div>
    @push('head')
        @vite('Modules/Litabmas/Resources/assets/sass/reports/show.scss')
        @vite('Modules/Litabmas/Resources/assets/js/reports/show.js')
    @endpush

    <div class="container" style="margin-left: 30%; margin-right: 30%; margin-top: 2%;">
        <div class="main__header">
            <div class="main__location">
                <ul class="breadcrumb">
                    <li class="breadcrumb__item">
                        <a href="">
                            <span class="icon icon-home-solid"></span>
                        </a>
                    </li>
                    <li class="breadcrumb__item active">Laporan</li>
                    <li class="breadcrumb__item active">Monitoring Pendanaan</li>
                </ul>
                <div class="main__wrapper">
                    <h1 class="main__title">Laporan Monitoring Pendanaan</h1>
                </div>
            </div>
        </div>
        @php
            $link = route('laporan-pendanaan-kegiatan.generate');
        @endphp
        <x-core::form id="form_list" :action="$link">
            <x-core::layouts.html.alert />
            <div class="col-12 d-flex">
                <div class="card card_form" id="">
                    <div class="card__body">
                        <div class="grid cols-1">
                            @foreach ($fields as $item)
                                @php
                                    $item['name'] ??= $item['field'];
                                    unset($item['field']);

                                    $attributes = Page::buildAttributes($item);
                                @endphp
                                <x-core::controls.form {{ $attributes }} />
                            @endforeach
                        </div>

                        <div class="footer-btn pull-right d-flex">
                            <button type="button" class="btn btn_primary" wire:click="goSubmitReport" style="">
                                <span class="icon icon-eye"></span>
                                Tampilkan
                            </button>
                            <button type="button" class="btn btn_primary" wire:click="goSubmitBlankReport" style="">
                                <span class="icon icon-arrow-top-right-on-square"></span>
                                Lihat di Tab Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-core::form>
        <br><br><br><br>
    </div>
    @script
        <script>
            Livewire.on('act-form', (event) => {
                let isBlank = event[0].blank;

                if (isBlank) {
                    // submit form_list with target blank
                    document.getElementById('form_list').setAttribute('target', '_blank');
                    document.getElementById('form_list').submit();
                    document.getElementById('form_list').removeAttribute('target');
                } else {
                    // submit form_list
                    document.getElementById('form_list').submit();
                }
            });
        </script>
    @endscript
</div>
