@php
    use Modules\Litabmas\Models\PengajuanPendanaanJadwalPresentasi;
    use Modules\Gate\Models\Role;
    use Modules\Core\Helpers\Format;
    use Carbon\Carbon;

    $canCreate = false;
    $role = auth()->user()->kode_role;
    if ($role == Role::ROLE_LITABMAS_ADMIN_LPPM) {
        $canCreate = true;
    }
@endphp

<div style="display: flex; flex-direction: column; gap: 1rem;">
    @pushOnce('head')
        {{-- [START] Vendor Flatpickr --}}
        <link rel="stylesheet" href="{{ asset('vendor/flatpickr-4.6.13/dist/flatpickr.min.css') }}">
        <script src="{{ asset('vendor/flatpickr-4.6.13/dist/flatpickr.min.js') }}"></script>
        {{-- [END] Vendor Flatpickr --}}

        @vite('Modules/Litabmas/Resources/assets/sass/dashboard/multi-role.scss')
        @vite('Modules/Litabmas/Resources/assets/sass/dashboard/dosen.scss')

        @vite('resources/scss/custom-utils.scss')

        <style>
            .row-pendanaan {
                display: flex;
                justify-content: flex-start;
                gap: 10px;
                align-items: center;
                margin-bottom: 10px;
            }

            .label-pendanaan {
                font-weight: bold;
                color: #333;
                font-size: 15px;
                width: 40%;
            }

            .value-pendanaan {
                color: #333;
                text-align: left;
                width: 55%;
                font-size: 15px;
            }

            .status-pendanaan {
                color: red;
                font-weight: bold;
            }

            .container-pendanaan div.value-pendanaan:last-child {
                flex: 1;
            }
        </style>
    @endPushOnce

    {{-- [START] Header --}}
    <div class="main__wrapper">
        <div class="grid" style="z-index: 501">
            <div class="col-12 col-sm-5 col-md-7">
                <h1 class="main__title">Klaster Pendanaan yang sedang dibuka</h1>
            </div>
        </div>
    </div>
    <div class="grid">
        <div class="col-12 col-sm-4 col-md-3">
            <div class="form-control">
                <div class="form-control__group">
                    <span data-input-icon="search"></span>
                    <input class="form-control__input" type="search" value="" placeholder="Cari data ..."
                        wire:model.live.debounce.500ms="filterSearch">
                    <span wire:click="resetSearch"></span>

                </div>
            </div>
        </div>
        <div class="col-12 col-sm-8 col-md-9">
            <div class="box-table__wrapper">
                <div class="grid cols-1 cols-sm-2 cols-md-3">
                    <div class="col-3" style="display: flex; gap: 10px;">
                        <div class="choices"></div>
                        <div class="choices"></div>

                        <x-core::controls.select label="Jenis Pendanaan" purpose="filter"
                            wire:model.change="filterJenisKlaster" :options="['penelitian' => 'Penelitian', 'pengabdian_masyarakat' => 'Pengabdian']" :selected="$filterJenisKlaster" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- [END] Header --}}
    @if (count($klasters) == 0)
        <div class="col-12" style="background: white; border: 1px; border-radius: 8px;border-style: solid;border-color: #E3E8EF;">
            <x-core::handler :title="'Belum Ada Klaster Pendanaan'" :subtitle="'Saat ini belum ada klaster pendanaan yang tersedia. Klaster yang dibuka akan muncul di sini setelah diumumkan'" :canCreate="$canCreate" />
        </div>
    @else
        {{-- [START] Sections --}}
        <div class="grid cols-1 cols-sm-2 cols-md-3">

            @foreach ($klasters as $item)
                @php
                    //format to 2 Okt - 15 Nov 2023
                    $tanggalMulaiPendaftaran = Carbon::parse($item->mulai_pendaftaran);
                    $tanggalSelesaiPendaftaran = Carbon::parse($item->akhir_pendaftaran);
                    $tanggalMulaiPendaftaran = $tanggalMulaiPendaftaran->translatedFormat('j M');
                    $tanggalSelesaiPendaftaran = $tanggalSelesaiPendaftaran->translatedFormat('j M Y');
                    $combinedTanggal = $tanggalMulaiPendaftaran . ' - ' . $tanggalSelesaiPendaftaran;
                @endphp
                <div class="card card_item">
                    <div class="card__item-body">
                        <h3 class="card__item-title">
                            {{ $item->nama_klaster }}

                            @if (in_array($item->id, $listIdProposalDiajukan))
                                <span class="badge badge_secondary-success badge_sm">
                                    <span class="icon icon-check-circle"></span>
                                    Diajukan
                                </span>
                            @endif
                        </h3>
                        <div class="container-pendanaan">
                            <div class="row-pendanaan">
                                <span class="label-pendanaan">Jenis Pendanaan</span>
                                <span class="">:</span>
                                <span class="value-pendanaan">{{ str_replace('_', ' ', ucfirst($item->kode_jenis_pendanaan)) }}</span>
                            </div>
                            <div class="row-pendanaan">
                                <span class="label-pendanaan">Sumber Pendanaan</span>
                                <span class="">:</span>
                                <span class="value-pendanaan">{{ $item->nama_sumber_pendanaan }}</span>
                            </div>
                            <div class="row-pendanaan">
                                <span class="label-pendanaan">Batas Pengajuan Dana</span>
                                <span class="">:</span>
                                <span class="value-pendanaan">Rp{{ Format::numberAbbv($item->maksimal_anggaran) }}</span>
                            </div>
                            <div class="row-pendanaan">
                                <span class="label-pendanaan">Kategori Pendanaan</span>
                                <span class="">:</span>
                                <span class="value-pendanaan">{{ ucfirst($item->kategori_klaster) }}</span>
                            </div>
                            <div class="row-pendanaan">
                                <span class="label-pendanaan">Tanggal Pendaftaran</span>
                                <span class="">:</span>
                                <span class="value-pendanaan">{{ $combinedTanggal }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card__item-footer">
                        <span class="card__item-total">
                            Proposal yang sudah mengajukan : {{ $item->total_pengajuan }}
                        </span>
                            <a href="{{ route('litabmas.pengumuman-klaster.show', $item->id) }}"
                                class="btn btn_link btn_xs">Lihat Detail</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="btn-wrapper">
            @if (count($klasters) > 9)
                <a href="#" class="btn btn_outline btn_xs">
                    Load More
                </a>
            @endif
        </div>
        {{-- [END] Sections --}}
    @endif

</div>
