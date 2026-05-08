@php
    use Modules\Core\Helpers\Page;
    use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;
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
    $action = route('litabmas.penentuan-pendanaan.jadwal-presentasi.index', $idPengajuanPendanaan);
    $backAction = $action; // tidak ada tampilan show detail
    if (!empty($resourceId)) {
        $method = 'PUT';
        $action .= '/' . $resourceId;
    }

    // set static alert
    if (!$sumberPendanaanMemilikiPresentasiProgress && !$sumberPendanaanMemilikiPresentasiOutput) {
        // tidak memiliki agenda presentasi progress maupun output
        $staticAlert = [
            'message' => 'Sumber pendanaan proposal ini tidak menggunakan agenda presentasi progress report maupun presentasi output.',
            'type' => 'warning',
            'dismissible' => false
        ];
    } elseif (!$sumberPendanaanMemilikiPresentasiProgress && $sumberPendanaanMemilikiPresentasiOutput) {
        // hanya memiliki agenda presentasi output
        $staticAlert = [
            'message' => 'Sumber Pendanaan proposal ini tidak menggunakan agenda presentasi progress report,
                hanya menggunakan presentasi output. Silahkan masukkan jadwal presentasi output.',
            'type' => 'helper',
            'dismissible' => false
        ];
    } elseif ($sumberPendanaanMemilikiPresentasiProgress && !$sumberPendanaanMemilikiPresentasiOutput) {
        // hanya memiliki agenda presentasi progress
        $staticAlert = [
            'message' => 'Sumber Pendanaan proposal ini tidak menggunakan agenda presentasi output,
                hanya menggunakan presentasi progress report. Silahkan masukkan jadwal presentasi progress report.',
            'type' => 'helper',
            'dismissible' => false
        ];
    }
@endphp
<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    <x-core::form :$method :$action>
        <div class="form-nav">
            <div class="form-nav__left">
                <a href="{{ $backAction }}" class="btn btn_outline">
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

            @if (!empty($staticAlert))
                <x-core::layouts.html.alert :data="$staticAlert" />
            @endif

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

    @pushonce('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const handleCheckTipeKegiatan = () => {
                    // tempat dan link kegiatan, query selector by name
                    const formTempatKegiatan = document.querySelector('input[name="tempat_pelaksanaan"]');
                    const formLinkKegiatan = document.querySelector('input[name="link_presentasi_kegiatan"]');

                    // get value checked dari radio dengan id 'tipe_kegiatan_offline'
                    const tipeKegiatanGroup = document.querySelector('input[name="tipe_kegiatan"]:checked');

                    // check value dari tipe yg di checked
                    if (tipeKegiatanGroup.value === '{{ PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_OFFLINE }}') {
                        formTempatKegiatan.parentElement.parentElement.style.display = 'block';
                        formLinkKegiatan.parentElement.parentElement.style.display = 'none';

                        formLinkKegiatan.removeAttribute('required');
                        formTempatKegiatan.setAttribute('required', 'required');
                    } else if (tipeKegiatanGroup.value === '{{ PengajuanPendanaanJadwalPresentasi::TIPE_KEGIATAN_ONLINE }}') {
                        formTempatKegiatan.parentElement.parentElement.style.display = 'none';
                        formLinkKegiatan.parentElement.parentElement.style.display = 'block';

                        formTempatKegiatan.removeAttribute('required');
                        formLinkKegiatan.setAttribute('required', 'required');
                    }
                }

                handleCheckTipeKegiatan();
                document.addEventListener("change", (e) => {
                    handleCheckTipeKegiatan();
                });
            });
        </script>
    @endpushonce
</x-core::layouts.outer>
