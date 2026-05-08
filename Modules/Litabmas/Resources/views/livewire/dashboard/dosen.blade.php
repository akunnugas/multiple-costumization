<div class="container">
    <div style="margin-top: -4rem;">
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @pushOnce('head')
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
                        width: 40%;
                    }

                    .value-pendanaan {
                        color: #333;
                        text-align: left;
                        width: 55%;
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

            @php
                $linkKlaster = $isDosen
                    ? route('litabmas.pengumuman-klaster.index')
                    : route('litabmas.klaster-pendanaan.index');
            @endphp

            <div class="col-12" wire:ignore>
                <nav-widget module-code="litabmas"></nav-widget>
            </div>

            {{-- [START] Card Sections --}}
            <div class="grid">
                {{-- [START] Klaster Pendanaan --}}
                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card__header">
                            <h4 class="card__title" style="font-size: 1rem;">
                                <div class="card__title-icon">
                                    <span class="icon icon-credit-card"></span>
                                </div>
                                Klaster Pendanaan yang sedang dibuka
                            </h4>
                            <a href="{{ $linkKlaster }}" class="btn btn_link">Lihat Semua</a>
                        </div>
                        <div class="card__body">
                            @if (count($dataKlaster) > 0)
                                <div class="grid cols-1 cols-sm-2 cols-md-2">
                                    @foreach ($dataKlaster as $item)
                                        @php
                                            $tanggalMulaiPendaftaran = Carbon\Carbon::parse($item->mulai_pendaftaran);
                                            $tanggalSelesaiPendaftaran = Carbon\Carbon::parse($item->akhir_pendaftaran);
                                            $tanggalMulaiPendaftaran = $tanggalMulaiPendaftaran->translatedFormat('j M');
                                            $tanggalSelesaiPendaftaran = $tanggalSelesaiPendaftaran->translatedFormat(
                                                'j M Y',
                                            );
                                            $combinedTanggal =
                                                $tanggalMulaiPendaftaran . ' - ' . $tanggalSelesaiPendaftaran;
                                        @endphp
                                        <div class="card card_item">
                                            <div class="card__item-body">
                                                <h3 class="card__item-title">
                                                    {{ $item->nama_klaster }}

                                                    @if (in_array($item->id, $listIdKlasterProposalDiajukan))
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
                                                <span>
                                                    Proposal yang sudah mengajukan : {{ $item->total_pengajuan }}
                                                </span>
                                                @php
                                                    $linkDetail = $isDosen
                                                        ? route('litabmas.pengumuman-klaster.show', $item->id)
                                                        : route('litabmas.klaster-pendanaan.show', $item->id);
                                                @endphp
                                                <a href="{{ $linkDetail }}" class="btn btn_link btn_xs">Lihat
                                                    Detail</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-list" style="padding-left: 2rem; padding-right: 2rem;">
                                    <div class="empty-list__wrapper">
                                        <div class="empty-list__content" style="align-items:center;">
                                            <div class="empty-list__inner">
                                                <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                                                <h1 style="text-align: start;">
                                                    Tidak ada Klaster Pendanaan
                                                </h1>
                                                <p style="text-align: start; max-width: 550px;">
                                                    Belum ada klaster pendanaan yang dibuka
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- [END] Klaster Pendanaan --}}

                {{-- [START] Pengumuman --}}
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card__header">
                            <h2 class="card__title" style="font-size: 1rem;">
                                <div class="card__title-icon">
                                    <span class="icon icon-speaker-wave"></span>
                                </div>
                                Pengumuman
                            </h2>
                            <a href="{{ route('litabmas.pengumuman-pendanaan.index') }}" class="btn btn_link">Lihat
                                Semua</a>
                        </div>
                        <div class="card__body">
                            @if (count($dataPengumuman) > 0)
                                <div class="grid cols-1">
                                    @foreach ($dataPengumuman as $item)
                                        <div class="card card_column"
                                            onclick="location.href = '{{ route('litabmas.pengumuman-pendanaan.show', $item->id) }}'"
                                            style="cursor: pointer;">
                                            <div class="card__column-body">
                                                <h3 class="card__column-title">
                                                    {{ $item->judul }}
                                                </h3>
                                                <div class="card__column-subtitle">
                                                    {{ Carbon\Carbon::parse($item->waktu_dipublikasi)->translatedFormat('d F Y') }}
                                                </div>
                                                <div class="card__column-subtitle">
                                                    {!! html_entity_decode($item->informasi) !!}
                                                </div>
                                            </div>
                                            @if ($item->id_dokumen_lampiran)
                                                @php
                                                    $simpleFile = Modules\DMS\Models\Dokumen::where(
                                                        'id',
                                                        $item->id_dokumen_lampiran,
                                                    )->first();
                                                    $ext = $simpleFile->extension_versi_terbaru;
                                                    $ext = $ext == 'docx' ? 'doc' : $ext;
                                                    $assetUrl = asset("images/$ext-solid.svg");
                                                    $tempUrl = $simpleFile->lastVersionTemporaryUrl();
                                                @endphp
                                                <div class="card__column-side">
                                                    <a href="{{ $tempUrl }}" class="btn btn_icon btn_outline btn_xs">
                                                        <img src="{{ $assetUrl }}" alt="">
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-list" style="padding-left: 2rem; padding-right: 2rem;">
                                    <div class="empty-list__wrapper">
                                        <div class="empty-list__content" style="align-items:center;">
                                            <div class="empty-list__inner">
                                                <img src="/v2/images/empty-state.png" width="200px" alt="illustration">
                                                <h1 style="text-align: start;">
                                                    Tidak ada Pengumuman
                                                </h1>
                                                <p style="text-align: start; max-width: 550px;">
                                                    Belum ada pengumuman terbaru
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- [END] Pengumuman --}}
            </div>
            {{-- [END] Card Sections --}}

            @if ($isDosen)
                {{-- [START] Jadwal Sections --}}
                <div class="grid">
                    <div class="col-12 col-md-12">
                        <div class="card">
                            <div class="card__header">
                                <h2 class="card__title">
                                    <div class="card__title-icon">
                                        <span class="icon icon-calendar-days"></span>
                                    </div>
                                    Jadwal LITABMAS
                                </h2>
                            </div>
                            <div class="card__body">
                                <div id="calendar" wire:ignore></div>

                                <div class="dropdown" id="eventDropdown">
                                    <button type="button" class="btn btn_primary" data-toggle="dropdown"
                                        style="display: none;">Dropdown Menu</button>
                                    <div class="dropdown__box">
                                        <ul class="dropdown__list">
                                            <li class="dropdown__item">
                                                <a href="/pages/examples/layout/form.html">Form</a>
                                            </li>
                                            <li class="dropdown__item">
                                                <a href="/pages/examples/layout/detail.html">Detail</a>
                                            </li>
                                            <li class="dropdown__item">
                                                <a href="/pages/examples/layout/list.html">Table</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- [END] Jadwal Sections --}}
            @endif

            <script src="{{ asset('vendor/fullcalendar-6.1.15/dist/index.global.min.js') }}"></script>

            @php
                $dateArray = [];
                foreach ($dataJadwalProposal as $jadwal) {
                    $dateArray[] = [
                        'start' => $jadwal['waktu_pelaksanaan'],
                        'end' => $jadwal['waktu_pelaksanaan'],
                        'title' => $jadwal['nama_kegiatan'],
                    ];
                }
            @endphp

            @php
                $authUser = auth()->user();
            @endphp

            @if (config('app.env') !== 'local' && !$authUser->is_internal)
                @php
                    $isLiveChatEnabled = in_array($authUser->kode_role, [
                        Modules\Gate\Models\Role::ROLE_ADMINPT,
                        Modules\Gate\Models\Role::ROLE_ADMIN_PENJAMINAN_MUTU,
                        Modules\Gate\Models\Role::ROLE_LITABMAS_ADMIN_LPPM,
                        Modules\Gate\Models\Role::ROLE_ADMIN_KERJASAMA
                    ]);
                @endphp

                @if ($isLiveChatEnabled)
                    @include('core::components.layouts.livechat')
                @endif
            @endif

            <script>
                let currentYear = new Date().getFullYear();
                let currentMonth = new Date().getMonth() + 1;

                var loadedYear = [currentYear];
                var loadedMonth = [currentMonth];

                document.addEventListener('DOMContentLoaded', function() {
                    var calendarEl = document.getElementById('calendar');
                    var events = @json($dateArray);
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        views: {
                            dayGridMonth: { // name of view
                                titleFormat: {
                                    year: 'numeric',
                                    month: 'long',
                                }
                            },
                        },
                        locale: 'id',
                        timeZone: 'local',
                        headerToolbar: {
                            left: '',
                            center: 'prev,title,next',
                            right: '',
                        },
                        eventTimeFormat: { // like '14:30:00'
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        },
                        events: (function() {
                            // If events are more than 3, show only the first 3 and add a "Lihat Lainnya" event
                            if (events.length > 3) {
                                var visibleEvents = events.slice(0, 3);
                                visibleEvents.push({
                                    start: events[2].start, // start time of the last visible event
                                    title: 'Lihat Lainnya',
                                    isMoreLink: true, // custom property to identify this event
                                    color: '#f39c12', // optional: different color for the "Lihat Lainnya" link
                                });
                                return visibleEvents;
                            } else {
                                return events;
                            }
                        })(),
                        eventClick: function(info) {
                            if (info.event.extendedProps.isMoreLink) {
                                // Show the rest of the events
                                calendar.removeAllEvents();
                                calendar.addEventSource(allEvents);
                            } else {
                                var dropdown = document.getElementById('eventDropdown');
                                var button = dropdown.querySelector('button[data-toggle="dropdown"]');
                                var dropdownBox = dropdown.querySelector('.dropdown__box');

                                dropdown.style.display = 'block';
                                dropdown.style.left = info.jsEvent.pageX + 'px';
                                dropdown.style.top = info.jsEvent.pageY + 'px';

                                button.classList.add('show');
                                dropdownBox.classList.add('show');

                                button.setAttribute('aria-expanded', 'true');

                                info.jsEvent.preventDefault();
                            }
                        }
                    });

                    calendar.render();

                    calendar.on('datesSet', function(info) {
                        let date = info.view.currentStart;
                        let year = date.getFullYear();
                        let month = date.getMonth() + 1;
                        if (year != currentYear || month != currentMonth) {
                            if (!loadedYear.includes(year) || !loadedMonth.includes(month)) {
                                @this.call('fetchJadwalProposal', year, month);
                            }
                        }
                    });
                });

                // // Menyembunyikan dropdown saat mengklik di luar
                // document.addEventListener('click', function(event) {
                //     var dropdown = document.getElementById('eventDropdown');
                //     if (!event.target.closest('.dropdown')) {`
                //         dropdown.style.display = 'none';
                //     }
                // });
            </script>

            @script
            <script>
                Livewire.on('render-calendar', (event) => {
                    event[0].data.foreach((item) => {
                        calendar.addEvent(item);
                    });
                    loadedYear.push(event[0].year);
                    loadedMonth.push(event[0].month);
                });
            </script>
            @endscript
        </div>
    </div>
</div>