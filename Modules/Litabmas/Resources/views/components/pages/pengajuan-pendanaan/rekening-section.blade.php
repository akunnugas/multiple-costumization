@props([
    'title' => null,
    'icon' => null,
    'showCollapseInSection' => false,
    'state' => [],
    'fields' => [],
    'alertRekening' => [],
    'apakahSnkDisetujui' => false,
    'headerInfoKlaster' => [],
])

<x-core::layouts.create.card :$title :$icon :$showCollapseInSection>
    <x-core::layouts.html.alert :data="$alertRekening"/>

    @include('litabmas::components.pages.pengajuan-pendanaan.header.header-pengajuan')

    @php
        // bagi $fields jadi 2, $firstFields index 0 - 1
        $firstFields = array_slice($fields, 0, 2);
        $secondFields = array_slice($fields, 2);
    @endphp

    @foreach($firstFields as $item)
        @php
            $item['name'] ??= $item['field'];
            unset($item['field']);

            $attributes = Page::buildAttributes($item);
        @endphp
        <x-core::controls.form {{ $attributes }} />
    @endforeach

    {{-- @if($linkTemplateRab)
        <a class="link" href="{{ $linkTemplateRab }}" download="rab_document" target="_blank" rel="noopener">
            <span class="icon icon-arrow-down-tray-mini"></span>
            Lihat Template RAB
        </a>
        <hr class="dashed"/>
    @endif --}}

    @foreach($secondFields as $item)
        @php
            $item['name'] ??= $item['field'];
            unset($item['field']);

            $attributes = Page::buildAttributes($item);
        @endphp
        <x-core::controls.form {{ $attributes }} />
    @endforeach
    <hr class="dashed"/>

    <div class="form-control">
        <label for="form-control-agenda-kegiatan" class="form-control__label"
               style="padding-bottom: 0;">
            Pernyataan pengusul bantuan
            <span class="important">*</span>
        </label>
        <div class="form-control__helper">
            Dengan ini kami menyatakan bahwa proposal bantuan berikut:
        </div>
    </div>
    <div class="form-control">
        <div class="checkbox">
            <input type="checkbox" class="form-control__checkbox" id="check-snk" name="apakahSnkDisetujui"
                   wire:model="apakahSnkDisetujui">
            <label for="check-snk" class="form-control__label-checkbox">
                Kami tidak sedang menerima bantuan dari pihak lain, dan jika ada, kami siap untuk
                dikeluarkan dari proses pengelolaan bantuan penelitian; proposal kami bebas dari plagiat dan kami akan
                mematuhi semua aturan dan petunjuk yang berlaku.
            </label>
        </div>
    </div>
    @error('snk')
        <div class="form-control__helper error util_pt-0" >
            {{ $message }}
        </div>
    @enderror
</x-core::layouts.create.card>
