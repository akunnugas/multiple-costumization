<div class="container">
    @php
        function checkListSVG() {
            echo '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.00156 13.3996C10.5362 13.3996 13.4016 10.5342 13.4016 6.99961C13.4016 3.46499 10.5362 0.599609 7.00156 0.599609C3.46694 0.599609 0.601562 3.46499 0.601562 6.99961C0.601562 10.5342 3.46694 13.3996 7.00156 13.3996ZM10.0868 5.55251C10.2817 5.28452 10.2225 4.90927 9.95446 4.71437C9.68647 4.51946 9.31122 4.57871 9.11632 4.84671L6.32932 8.67884L4.82583 7.17534C4.59151 6.94103 4.21161 6.94103 3.9773 7.17534C3.74298 7.40966 3.74298 7.78956 3.9773 8.02387L5.9773 10.0239C6.10138 10.148 6.27356 10.2115 6.44849 10.1978C6.62343 10.184 6.78359 10.0944 6.8868 9.95251L10.0868 5.55251Z" fill="#15B79E"/></svg>';
        }

        function crossListSVG() {
            echo '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.00156 13.3996C10.5362 13.3996 13.4016 10.5342 13.4016 6.99961C13.4016 3.46499 10.5362 0.599609 7.00156 0.599609C3.46694 0.599609 0.601562 3.46499 0.601562 6.99961C0.601562 10.5342 3.46694 13.3996 7.00156 13.3996ZM5.62583 4.77534C5.39151 4.54103 5.01161 4.54103 4.7773 4.77534C4.54298 5.00966 4.54298 5.38956 4.7773 5.62387L6.15303 6.99961L4.7773 8.37534C4.54298 8.60966 4.54298 8.98956 4.7773 9.22387C5.01161 9.45819 5.39151 9.45819 5.62583 9.22387L7.00156 7.84814L8.3773 9.22387C8.61161 9.45819 8.99151 9.45819 9.22583 9.22387C9.46014 8.98956 9.46014 8.60966 9.22583 8.37534L7.85009 6.99961L9.22583 5.62387C9.46014 5.38956 9.46014 5.00966 9.22583 4.77534C8.99151 4.54103 8.61161 4.54103 8.3773 4.77534L7.00156 6.15108L5.62583 4.77534Z" fill="#EC3D27"/></svg>';
        }
    @endphp

    <div class="full-page-loader" wire:loading.flex wire:loading.delay.longest
        wire:target="pembimbing_submitForm,similarity_submitForm,reviewer_submitForm,jadwalProposal_submitForm,submitRemoveForm,overview_submitDokumenSK">
        <div class="loader">
            <span class="loader__spinner"></span>
        </div>
    </div>

    @php
        $homeUrl = $currentData['kode_jenis_pendanaan'] == 'Penelitian' ? route('litabmas.pengajuan-pendanaan.index') : route('litabmas.pengajuan-pengabdian.index');
    @endphp

    <div class="container">
        <div class="card">
            <div class="card__header">
                <ul class="breadcrumb">
                    <li class="breadcrumb__item">
                        <span class="icon icon-home-mini"></span>
                    </li>
                    <li class="breadcrumb__item active">
                        <a href="{{ $homeUrl }}">
                            Penelitian & Pengabdian
                        </a>
                    </li>
                    <li class="breadcrumb__item">
                        Proposal {{ $currentData['kode_jenis_pendanaan'] === Modules\Litabmas\Enums\JenisPendanaanEnum::CODE_PENELITIAN ? 'Penelitian' : 'Pengabdian' }}
                    </li>
                    <li class="breadcrumb__item active">
                        {{ $subtitle }}
                    </li>
                    <li class="breadcrumb__item active">
                        {{ $title }}
                    </li>
                </ul>

                <div class="button-group">
                    <div class="button-group__left">
                        <a class="btn btn_outline btn_xs" href="{{ $homeUrl }}">
                            Kembali ke List
                        </a>
                    </div>

                    <div class="button-group__mobile">
                        <a href="{{ $homeUrl }}"
                            class="btn btn_outline btn_icon btn_xs">
                            <span class="icon icon-arrow-left-solid"></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card__body">
                {{-- sidebar left --}}
                <div class="sidebar sidebar_within">
                    <div class="sidebar__header">
                        <h3 class="sidebar__title">
                            Proposal {{ $currentData['kode_jenis_pendanaan'] === Modules\Litabmas\Enums\JenisPendanaanEnum::CODE_PENELITIAN ? 'Penelitian' : 'Pengabdian' }}
                        </h3>

                        <div class="sidebar__action">
                            <button type="button" class="btn btn_icon">
                                <img src="{{ asset('images/icon/icon-layout.svg') }}" alt="">
                            </button>
                        </div>
                    </div>
                    <ul class="sidebar__list">
                        @foreach ($menu['items'] as $item)
                            <div style="margin-top: 10px;">
                                <h5 style="color: #0F6AF5;">{{ $item['label'] }}</h5>
                                @foreach ($item['items'] as $sub)
                                    @php
                                        $curPath = str_replace($menu['parent'] . '-', '', $sub['path']);
                                        $url =
                                            url('litabmas/' . str_replace('-' . $curPath, '', $sub['path'])) .
                                            '/' .
                                            $idProposalPendanaan .
                                            '/' .
                                            $curPath;
                                    @endphp
                                    <li class="sidebar__item">
                                        <a @class(['sidebar__link', 'active' => $curPath == $subResource]) href="{{ $url }}">
                                            <span class="sidebar__link-text">{{ $sub['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                                <br>
                                <hr>
                            </div>
                        @endforeach
                    </ul>
                </div>

                <div class="content" style="width: 70%; margin-left: 30px; margin-right: 30px;">

                    @include('litabmas::livewire.pengajuan-pendanaan.form-action')

                    @if ($urlInfo['id'] == 'overview')
                        @if (!is_null($timelineActive) && !$isDosen)
                            @if (!$isBypassDisabled && $timelineActive->kode_agenda == \Modules\Litabmas\Models\AgendaKegiatan::STEP_SELEKSI_ADMINISTRASI)
                                @if ($currentData['status_agenda_kegiatan'] === \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI
                                    && $currentData['status_similarity'] === \Modules\Litabmas\Models\PengajuanPendanaan::LOLOS_SIMILARITY_AI)

                                    @php
                                        $agendaReviewer = array_filter($timelines, function ($item) {
                                            return $item->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_FEEDBACK_REVIEWER;
                                        });
                                    @endphp

                                    <div class="alert alert_helper">
                                        <div class="alert__content">
                                            @if (!empty($agendaReviewer))
                                                <p>
                                                    Proposal telah dinyatakan <b>Lolos Administrasi</b>, silakan <a style="color: #0F6AF5;" href="{{ url('litabmas/pengajuan-pendanaan') . '/' . $idProposalPendanaan . '/reviewer' }}">klik disini</a> untuk menentukan Reviewer.
                                                </p>
                                            @else
                                                <p>
                                                    Proposal telah dinyatakan <b>Lolos Administrasi</b>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    <br>
                                @endif

                                @if ($currentData['status_agenda_kegiatan'] === \Modules\Litabmas\Models\PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI)
                                    <div class="alert alert_helper">
                                        <div class="alert__content">
                                            <p>
                                                Proposal telah dinyatakan <b>Tidak Lolos Administrasi</b> @if($currentData['status_similarity'] === \Modules\Litabmas\Models\PengajuanPendanaan::TIDAK_LOLOS_SIMILARITY_AI) karena nilai Similarity dan AI diatas batas minimum Lolos @else karena dokumen tidak lengkap @endif.
                                            </p>
                                        </div>
                                    </div>
                                    <br>
                                @endif
                            @endif
                        @endif
                    @endif

                    @if ($urlInfo['id'] != 'summary')
                        <div class="card card_details-primary">
                            <div class="grid">
                                <x-litabmas::layouts.detail.line :data="$data['utama']['items']" />
                            </div>
                        </div>
                        <br>
                    @endif

                    @include('litabmas::livewire.pengajuan-pendanaan.partials.' . $urlInfo['id'])
                </div>

                @include('litabmas::components.sidebar.timeline')
            </div>
        </div>
    </div>

    <x-core::modal title="Hapus {{ $title }}" variant="error" id="modal-remove"
        width="600px" wire:ignore.self>
        <x-core::form method="POST">
            <x-core::modal.body>
                Apakah Anda yakin ingin menghapus data <b>"{{ $removeTitle ?? '' }}"</b>? Karena data yang telah dihapus tidak dapat dikembalikan lagi.

                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Batalkan
                        </x-core::button>

                        <x-core::button variant="destructive" class="util_ml-8" wire:click="submitRemoveForm">
                            Hapus
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>
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

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
    </script>
    @script
        <script>
            Livewire.on('show-form-modal', (event) => {
                document.getElementById(event[0].id).classList.add("is-visible");

                // remove checked checkbox
                const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });

                // check checked checkbox
                for (let i = 0; i < event[0].fields.length; i++) {
                    if (event[0].fields[i].control == 'checkbox') {
                        for (let j = 0; j < event[0].fields[i].value.length; j++) {
                            let idCheckbox = event[0].fields[i].value[j];
                            let checkbox = document.querySelector(`input[type="checkbox"][value="${idCheckbox}"]`);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        }
                    }
                }

                // reset file input
                const files = document.querySelectorAll('input[type="file"]');
                files.forEach((file) => {
                    file.value = '';
                    file.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });

            Livewire.on('hide-form-modal', (event) => {
                document.getElementById(event[0].id).classList.remove("is-visible");
            });
        </script>
    @endscript
@endpush
</div>
