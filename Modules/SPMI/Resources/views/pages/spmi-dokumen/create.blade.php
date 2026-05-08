@php
    $subtitle ??= '';

    // default title
    if (empty($title)) {
        $title = (empty($resourceId) ? 'Tambah' : 'Edit') . ' Dokumen ' . $qualityType->nama_spmi_jenis_dokumen;
    }

    // parameter form
    if (empty($resourceId)) {
        $method = 'POST';
        $action = Page::indexURL();
    } else {
        $method = 'PUT';
        $action = Page::detailURL($resourceId);
    }

    // buat table of content jika advanced
    $toc = [];
    if (!empty(current($data)['items'])) {
        foreach ($data as $i => $section) {
            if (empty($section['id'])) {
                $section['id'] = Str::kebab($section['title']);
            }

            $toc[$section['id']] = $section['title'];
            $data[$i] = $section;
        }
    }
@endphp
<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    <x-core::form :$method :$action>
        <div class="form-nav">
            <div class="form-nav__left">
                <a href="{{ $action }}" class="btn btn_outline">
                    <span class="icon icon-arrow-left-mini"></span>
                    <span class="btn__text">Kembali</span>
                </a>
            </div>
            <div class="form-nav__middle">
                <ul class="form-nav__breadcrumb">
                    <li class="form-nav__breadcrumb-item">
                        <a href="#" class="form-nav__breadcrumb-btn">{{ $title }}</a>
                    </li>
                    <li class="form-nav__breadcrumb-diagonal"></li>
                    <li class="form-nav__breadcrumb-item active">
                        Tambah
                    </li>
                </ul>
            </div>
            <div class="form-nav__right">
                <div class="form-nav__wrapper">
                    <div class="form-nav__button-wrapper">
                        <button type="submit" class="btn btn_primary">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <x-core::layouts.main.container :$menu :$title :$subtitle>
            <x-core::layouts.html.alert style="margin-bottom:1rem" />
            <div class="grid">
                <div class="col-12">
                    <div class="card card_form" id="{{ Str::kebab($title) }}">
                        <div class="card__body">
                            <div class="grid cols-1">
                                @if (isset($qualityType))
                                    <div class="alert alert alert_helper">
                                        <div class="alert__content">
                                            <h4 class="alert__heading">{{ $qualityType->nama_spmi_jenis_dokumen }}</h4>
                                            <p>
                                                {{ $qualityType->deskripsi ?? $qualityType->deskripsi_singkat }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                                @foreach ($data as $item)
                                    @php
                                        $item['name'] ??= $item['field'];
                                        unset($item['field']);

                                        $attributes = Page::buildAttributes($item);
                                    @endphp
                                    <x-core::controls.form {{ $attributes }} />
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </x-core::layouts.main.container>
    </x-core::form>
</x-core::layouts.outer>
