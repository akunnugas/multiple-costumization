@php use Modules\Core\Helpers\Page; @endphp
@props([
    'data' => [],
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'resourceId' => null,
    'resourceTitle' => null,
])
@php
    // default title
    $actionLabelContext = empty($resourceId) ? 'Tambah' : 'Edit';
    if (empty($title)) {
        $title = $actionLabelContext . ' ' . $resourceTitle;
    }

    // parameter form
    if (empty($resourceId)) {
        $method = 'POST';
        $action = route('litabmas.pengajuan-pendanaan.aktivitas-penelitian.index', $idPengajuanPendanaan);
    } else {
        $method = 'PUT';
        $action = Page::detailURL($resourceId);
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
                    <li class="form-nav__breadcrumb-item active">
                        <a href="#" class="form-nav__breadcrumb-btn">{{ $title }}</a>
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
            <x-core::layouts.html.alert />
            <div class="grid">
                <div class="col-12">
                    <div {{ $attributes->merge(['class' => 'card card_form', 'id' => Str::kebab($title)]) }}>
                        @if (!empty($title))
                            <div class="card__header">
                                <div class="form-header">
                                    <div class="form-header__wrapper">
                                        <div class="form-header__avatar">
                                            <span class="icon icon-{{ $icon ?? 'cube' }}-mini"></span>
                                        </div>
                                        <div class="form-header__information">
                                            <h3 class="form-header__title">{{ $title }}</h3>
                                            <p class="form-header__subtitle">{{ $subtitle }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="card__body">
                            <div class="grid cols-1">
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
