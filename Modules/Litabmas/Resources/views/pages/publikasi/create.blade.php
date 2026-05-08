@php
    use Modules\Core\Helpers\Page;
    use Modules\Litabmas\Enums\PublikasiPenelitianEnum;
@endphp
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
    $method = 'POST';
    $action = Page::indexURL();
    if (!empty($resourceId)) {
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
                                @php
                                    $fieldJenisPublikasi = $fieldPengajuanPendanaan = null;
                                @endphp
                                @foreach ($data as $item)
                                    @php
                                        if ($item['field'] === 'jenis_publikasi') {
                                            $fieldJenisPublikasi = $item;
                                        }
                                        if ($item['field'] === 'id_pengajuan_pendanaan') {
                                            $fieldPengajuanPendanaan = $item;
                                        }

                                        $item['name'] ??= $item['field'];
                                        unset($item['field']);

                                        $attributes = Page::buildAttributes($item);
                                    @endphp
                                    <x-core::controls.form {{ $attributes }} />
                                @endforeach
                                @if(!empty($resourceId))
                                    <input type="hidden" name="jenis_publikasi" value="{{ $fieldJenisPublikasi['value'] }}">
                                    <input type="hidden" name="id_pengajuan_pendanaan" value="{{ $fieldPengajuanPendanaan['value'] }}">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-core::layouts.main.container>
    </x-core::form>

    @pushOnce('head')
        <style>
            .form-nav~.container {
                max-width: 45.875rem !important;
            }
        </style>
    @endPushOnce

    @if(empty($resourceId))
        @pushonce('scripts')
            <script>
                {{-- Hanya ketika create saja --}}
                document.addEventListener('DOMContentLoaded', function () {
                    const handleCheckTipeKegiatan = () => {
                        // inputan artikel:
                        const judulArtikelInput = document.querySelector('input[name="judul_artikel"]');
                        const situsPublikasiInput = document.querySelector('input[name="situs_publikasi_jurnal"]');
                        const volumeInput = document.querySelector('input[name="volume_dan_nomor_terbitan"]');
                        const urlArtikelInput = document.querySelector('input[name="url_artikel"]');

                        // inputan buku:
                        const judulBukuInput = document.querySelector('input[name="judul_buku"]');
                        const isbnInput = document.querySelector('input[name="isbn"]');
                        const penerbitInput = document.querySelector('input[name="penerbit_buku"]');
                        const tahunTerbitInput = document.querySelector('input[name="tahun_terbit_buku"]');

                        // get value checked dari radio dengan id 'tipe_kegiatan_offline'
                        const jenisPublikasi = document.querySelector('input[name="jenis_publikasi"]:checked');

                        // check value dari tipe yg di checked
                        if (jenisPublikasi.value === '{{ PublikasiPenelitianEnum::JENIS_ARTIKEL }}') {
                            judulArtikelInput.parentElement.parentElement.style.display = 'block';
                            situsPublikasiInput.parentElement.parentElement.style.display = 'block';
                            volumeInput.parentElement.parentElement.style.display = 'block';
                            urlArtikelInput.parentElement.parentElement.style.display = 'block';

                            judulBukuInput.parentElement.parentElement.style.display = 'none';
                            isbnInput.parentElement.parentElement.style.display = 'none';
                            penerbitInput.parentElement.parentElement.style.display = 'none';
                            tahunTerbitInput.parentElement.parentElement.style.display = 'none';

                            judulArtikelInput.setAttribute('required', 'required');
                            situsPublikasiInput.setAttribute('required', 'required');
                            volumeInput.setAttribute('required', 'required');
                            urlArtikelInput.setAttribute('required', 'required');

                            judulBukuInput.removeAttribute('required');
                            isbnInput.removeAttribute('required');
                            penerbitInput.removeAttribute('required');
                            tahunTerbitInput.removeAttribute('required');
                        } else if (jenisPublikasi.value === '{{ PublikasiPenelitianEnum::JENIS_BUKU }}') {
                            judulArtikelInput.parentElement.parentElement.style.display = 'none';
                            situsPublikasiInput.parentElement.parentElement.style.display = 'none';
                            volumeInput.parentElement.parentElement.style.display = 'none';
                            urlArtikelInput.parentElement.parentElement.style.display = 'none';

                            judulBukuInput.parentElement.parentElement.style.display = 'block';
                            isbnInput.parentElement.parentElement.style.display = 'block';
                            penerbitInput.parentElement.parentElement.style.display = 'block';
                            tahunTerbitInput.parentElement.parentElement.style.display = 'block';

                            judulArtikelInput.removeAttribute('required');
                            situsPublikasiInput.removeAttribute('required');
                            volumeInput.removeAttribute('required');
                            urlArtikelInput.removeAttribute('required');

                            judulBukuInput.setAttribute('required', 'required');
                            isbnInput.setAttribute('required', 'required');
                            penerbitInput.setAttribute('required', 'required');
                            tahunTerbitInput.setAttribute('required', 'required');
                        }
                    }

                    handleCheckTipeKegiatan();
                    document.addEventListener("change", (e) => {
                        handleCheckTipeKegiatan();
                    });
                });
            </script>
        @endpushonce
    @endif
</x-core::layouts.outer>
