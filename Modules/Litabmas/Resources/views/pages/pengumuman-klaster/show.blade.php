@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
    'subtitle' => null,
    'title' => null,
    'isFullwidth' => false,
    'disableEdit' => false,

    // Slot
    'action' => null,
    'outer' => null,

    // permission
    'canUpdate' => true,
])
@php
    use Modules\Litabmas\Models\AgendaKegiatan;
    use Modules\Litabmas\Models\KlasterPendanaan;
    use Carbon\Carbon;

    // default title
    $title ??= $resourceTitle;
    if (empty($subtitle) && !empty($title)) {
        $subtitle = 'Detail ' . $title;
    }

    $permission = request()->permission;
    if ($canUpdate && empty($permission['put'])) {
        $canUpdate = false;
    }

    //mapping data by field
    $dataFormated = collect($data[0]['items'])->mapWithKeys(function ($item) {
        return [$item['field'] => $item];
    });

    $pendaftaran = $agendaMapping->where('kode_agenda', AgendaKegiatan::STEP_PENDAFTARAN)->first();

    $waktuMulai = Carbon::parse($pendaftaran->waktu_mulai)->toDateString();
    $waktuSelesai = Carbon::parse($pendaftaran->waktu_selesai)->toDateString();
    $currentDate = now()->toDateString();

    $apakahDibuka = $waktuMulai <= $currentDate && $waktuSelesai >= $currentDate;

    $urlCreatePengajuan = null;
    if ($apakahDibuka) {
        $urlCreatePengajuan = route('litabmas.pengajuan-pendanaan.create');
        $urlCreatePengajuan .= '?x_kjp=' . $dataFormated['kode_jenis_pendanaan']['original'];
        $urlCreatePengajuan .= '&x_kis=' . $dataFormated['id_sumber_pendanaan']['original'];
        $urlCreatePengajuan .= '&x_kid=' . $resourceId;
    }

@endphp
<x-core::layouts.outer header-class="header_position-static" :$menu :$title>
    @pushOnce('head')
        @vite('resources/scss/layouts/_detail.scss')
        @vite('resources/scss/custom-utils.scss')
    @endPushOnce

    <div class="container">
        <div class="card">
            <div class="card__header">
                <ul class="breadcrumb">
                    <li class="breadcrumb__item">
                        <span class="icon icon-home-mini"></span>
                    </li>
                    <li class="breadcrumb__item">
                        <a href="{{ route('litabmas.pengumuman-klaster.index') }}">
                            Pengumuman Klaster
                        </a>
                    </li>
                    <li class="breadcrumb__item active">
                        {{ $title }}
                    </li>
                </ul>

                <div class="button-group">
                    <div class="button-group__left">
                        <a class="btn btn_outline btn_xs" href="{{ route('litabmas.pengumuman-klaster.index') }}">
                            Kembali ke List
                        </a>

                        @if ($urlCreatePengajuan)
                            <button class="btn btn_primary btn_xs" @if ($isLimitKetua || $isLimitAjuan) disabled @endif onclick="location.href = '{{ $urlCreatePengajuan }}'">
                                Ajukan Pendanaan
                            </button>
                        @endif
                    </div>

                    <div class="button-group__mobile">
                        <a href="{{ !empty($resourceId) ? Page::indexURL() : Page::backURL() }}"
                            class="btn btn_outline btn_icon btn_xs">
                            <span class="icon icon-arrow-left-solid"></span>
                        </a>

                        @if ($canUpdate && !$disableEdit)
                            <a class="btn btn_primary btn_icon btn_xs" href="{{ Page::editURL($resourceId) }}">
                                <span class="icon icon-pencil-solid"></span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card__body">
                <!-- Content -->
                <div @class(['content', 'content_full' => true])>
                    <div class="content__header">
                        <h3 class="content__title">
                            Informasi Klaster Pendanaan
                        </h3>
                    </div>
                    @if ($isLimitKetua)
                        <div class="alert alert_danger">
                            <div class="alert__content">
                                <p>
                                    Anda sudah mencapai batas limit menjadi ketua yaitu <b>{{ $maxKetua }} kali</b> dalam periode ini.
                                </p>
                            </div>
                        </div>
                        <br>
                    @endif
                    @if ($isLimitAjuan)
                        <div class="alert alert_warning">
                            <div class="alert__content">
                                <p>
                                    {!! $messageLimit !!}
                                </p>
                            </div>
                        </div>
                        <br>
                    @endif
                    <div class="card card_details-primary">
                        <div class="grid">
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                <div class="grid cols-1">
                                    <div class="row-data">
                                        <label class="row-data__name">Nama Klaster Pendanaan</label>
                                        <span class="row-data__value"><span
                                                class="row-data__colon">:</span>{{ $dataFormated['nama_klaster']['text'] }}</span>
                                    </div>
                                    <div class="row-data">
                                        <label class="row-data__name">Jenis Pendanaan</label>
                                        <span class="row-data__value"><span
                                                class="row-data__colon">:</span>{{ $dataFormated['kode_jenis_pendanaan']['text'] }}</span>
                                    </div>
                                    <div class="row-data">
                                        <label class="row-data__name">Sumber Pendanaan</label>
                                        <span class="row-data__value"><span
                                                class="row-data__colon">:</span>{{ $dataFormated['id_sumber_pendanaan']['text'] }}</span>
                                    </div>
                                    <div class="row-data">
                                        <label class="row-data__name">Batas Pengajuan Dana</label>
                                        <span class="row-data__value"><span class="row-data__colon">:</span>
                                            <x-litabmas::fields.format_currency :value="$dataFormated['maksimal_anggaran']['text']" :data="['mata_uang' => $dataFormated['mata_uang']['text']]" />
                                        </span>
                                    </div>
                                    {{-- <div class="row-data">
                                        <label class="row-data__name">Pengelola Dana</label>
                                        <span class="row-data__value"><span
                                                class="row-data__colon">:</span>{{ $pengelolaBantuan->nama_unit }}</span>
                                    </div> --}}
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                                <div class="grid cols-1">
                                    <div class="row-data">
                                        <label class="row-data__name">Kategori Pendanaan</label>
                                        <span class="row-data__value"><span
                                                class="row-data__colon">:</span>{{ $dataFormated['kategori_klaster']['text'] }}</span>
                                    </div>
                                    @if ($dataFormated['kategori_klaster']['original'] !== KlasterPendanaan::KATEGORI_INDIVIDU)
                                        <div class="row-data">
                                            <label class="row-data__name">Jumlah Anggota</label>
                                            <span class="row-data__value"><span
                                                    class="row-data__colon">:</span>{{ $dataFormated['minimal_anggota']['text'] }}
                                                - {{ $dataFormated['maksimal_anggota']['text'] }} Anggota</span>
                                        </div>
                                    @endif
                                    {{-- <div class="row-data">
                                        <label class="row-data__name">Status Pendanaan</label>
                                        <span class="row-data__value"><span class="row-data__colon">:</span><span
                                                class="badge badge_outline-{{ $apakahDibuka ? 'success' : 'danger' }} badge_sm">
                                                {{ $apakahDibuka ? 'Dibuka' : 'Ditutup' }}
                                            </span></span>
                                    </div> --}}
                                    <div class="row-data">
                                        <label class="row-data__name">Proposal Sudah Mengajukan</label>
                                        <span class="row-data__value"><span
                                                class="row-data__colon">:</span>{{ $totalPendanaan->total_pendanaan }}</span>
                                    </div>
                                    <div class="row-data">
                                        <label class="row-data__name">Maksimal Pengajuan Proposal</label>
                                        <span class="row-data__value">
                                            <span class="row-data__colon">:</span>
                                            <strong>{{ $klasterPendanaan->maksimal_ajuan_per_user }}x</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="content__header" style="padding-top: 1.5rem">
                        <h3>
                            Bidang Ilmu & Tema
                        </h3>
                        <br>
                    </div>
                    <div class="table-max table-max_absolute grid cols-1">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Bidang Ilmu</th>
                                    <th>Tema</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bidangIlmu as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item['nama_bidang_ilmu'] }}</td>
                                        <td>
                                            <ul style="padding-left: 1rem">
                                                @foreach ($item['tema'] as $t)
                                                    <li style="padding-top: 0.25rem">{{ $t['nama_tema'] }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr class="breakline">

                    <div class="grid">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <h3>
                                Luaran yang wajib dipilih
                            </h3>
                            <br>
                            <div class="table-max table-max_absolute grid cols-1">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Luaran</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($outputWajib as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_output }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            <h3>
                                Publikasi wajib dipilih
                            </h3>
                            <br>
                            <div class="table-max table-max_absolute grid cols-1">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Publikasi</th>
                                            <th>Batas Pengumpulan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($outcomeWajib as $item)
                                            @php
                                                $startDatePeriod = Carbon::parse($periodePendanaan->tanggal_mulai);
                                                $formatedCollectionLimit = $startDatePeriod->addYears((int) $item->batas_pengumpulan_outcome);
                                                $batas = $formatedCollectionLimit->translatedFormat('d F Y') . ' (' . $item->batas_pengumpulan_outcome . ' tahun)';
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_outcome }}</td>
                                                <td>{{ $batas }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr class="breakline">

                    <div class="content__header">
                        <h3>
                            Tahapan Kegiatan
                        </h3>
                    <br>
                    </div>
                    <div class="table-max table-max_absolute grid cols-1">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 1rem">No</th>
                                    <th>Tahapan Kegiatan</th>
                                    <th>Tanggal Tahapan Kegiatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($agendaMapping as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->nama_agenda }}</td>
                                        <td>{{ $item->tanggal_agenda }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($urlCreatePengajuan)
                        <div class="button-group" style="justify-content: center; padding-top: 1.5rem;">
                            <button class="btn btn_primary btn_xs" @if ($isLimitKetua || $isLimitAjuan) disabled @endif onclick="location.href = '{{ $urlCreatePengajuan }}'">
                                Ajukan Pendanaan
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="text/javascript" src="{{ asset('js/validation.js') }}"></script>

        <script>
            const sidebarWithin = document.querySelector('.sidebar.sidebar_within');
            const mainContent2 = document.querySelector('.content');

            document.querySelector('.sidebar__action button').onclick = function() {
                sidebarWithin.classList.toggle('sidebar_within-collapsed');
                mainContent2.classList.toggle('content_large');

            }
        </script>
    @endpush
</x-core::layouts.outer>