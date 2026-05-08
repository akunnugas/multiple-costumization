@pushOnce('head')
    @vite('resources/scss/custom-utils.scss')
    <style>
        .card .card__body {
            padding-top: 0 !important;
        }

        .card__body .box-table__content {
            padding: 0 !important;
            border-top: none !important;
        }

        .form-nav~.container {
            max-width: 45.875rem !important;
        }
    </style>
@endPushOnce
@pushOnce('headVendor')
    <link href="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.snow.css') }}" rel="stylesheet">
    <script src="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.min.js') }}"></script>
@endPushOnce
@php
    $generalInformationSection = $data['informasi-umum'];
    $isianProposalSection = $data['isian-proposal'];
    $memberSection = $data['member'];
    $rekeningSection = $data['rekening'];
    if (!empty($edit)) {
        $backSubFooter = route('litabmas.pengajuan-pendanaan.show', $edit) . '/overview';
    }
@endphp

<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert :numberToc="'true'" :$backSubFooter>
    <x-slot:customAction>
        <div class="form-nav__button-wrapper">
            <x-core::button wire:click="draft" variant="outline" size="sm" type="button" :disabled="$forceCannotCreate">
                Simpan Draft
            </x-core::button>
        </div>
        <div class="form-nav__button-wrapper">
            <x-core::button wire:click="nextStep" type="button" size="sm" :disabled="$forceCannotCreate">
                @if ($steps == 4)
                    Simpan dan Ajukan
                @else
                    Simpan dan Lanjutkan
                @endif
            </x-core::button>
        </div>
    </x-slot:customAction>


    <div class="full-page-loader" id="page_loading" @if(!$isUploading) style="display: none;" @else style="display:flex; justify-content:center; align-items:center;" @endif>
        {{-- center of page --}}
        <div class="loader">
            <span class="loader__spinner"></span>
        </div>
    </div>

    @if (!empty($alertGeneralInfo))
        <div class="col-12">
            <x-core::layouts.html.alert :data="$alertGeneralInfo" />
        </div>
    @endif

    @if ($steps == 1)
        {{-- [Start] Informasi Umum --}}
        <x-litabmas::pages.pengajuan-pendanaan.informasi-umum-section :title="$generalInformationSection['title']" :icon="$generalInformationSection['icon']"
            :fields="$generalInformationSection['items']" :$showCollapseInSection :$idKlasterPendanaan :$requiredOutputs :$oldRecordOutput
            :$headerInfoKlaster />
        {{-- [End] Informasi Umum --}}
    @endif

    @if ($steps == 2)
        {{-- [Start] Isian Proposal --}}
        <x-litabmas::pages.pengajuan-pendanaan.isian-proposal-section :title="$isianProposalSection['title']" :icon="$isianProposalSection['icon']"
            :fields="$isianProposalSection['items']" :$apakahAdaIsianProposal :$kodeJenisPendanaan :$alertIsianProposal
            :$showCollapseInSection :$headerInfoKlaster />
        {{-- [End] Isian Proposal --}}
    @endif

    @if ($steps == 3)
        {{-- [Start] Data Peneliti --}}
        <x-litabmas::pages.pengajuan-pendanaan.anggota-section :title="$memberSection['title']" :icon="$memberSection['icon']" :$kategoriKlaster
            :$minimalAnggota :$maksimalAnggota :fields="$memberSection['items']" :$apakahAdaSumberPendanaan :$apakahAdaKlasterPendanaan
            :$record :$recordSavedAnggota :$alertAnggota :$showCollapseInSection :$apakahButuhApproveSemuaAnggota
            :idPengajuanPendanaan="$edit ?? null" :$headerInfoKlaster />
        {{-- [End] Data Peneliti --}}
    @endif

    @if ($steps == 4)
        {{-- [Start] Data Pendanaan --}}
        <x-litabmas::pages.pengajuan-pendanaan.rekening-section :title="$rekeningSection['title']" :icon="$rekeningSection['icon']" :fields="$rekeningSection['items']"
            :$edit :$apakahSnkDisetujui :$headerInfoKlaster/>
        {{-- [End] Data Pendanaan --}}
    @endif

    <x-core::modal title="Apakah Anda yakin mengajukan proposal ini?" variant="primary" id="modal-confirm-save"
        width="600px">
        <x-core::form method="POST">
            <x-core::modal.body>
                <x-core::alert variant="helper" :dismissable="false" class="util_mb-16">
                    @if ($apakahButuhApproveSemuaAnggota)
                        Proposal dapat berhasil diajukan setelah semua calon anggota dosen menerima tawaran.
                    @else
                        Pastikan semua data proposal benar sebelum melakukan pengajuan proposal
                    @endif
                </x-core::alert>
                @foreach ($confirmData as $key => $datas)
                    <div class="grid">
                        @foreach ($datas as $data)
                            <div class="col-12 col-sm-4 col-md-4 col-lg-4">
                                <label class="row-data__name">{{ $data['label'] }}</label>
                            </div>
                            <div class="col-12 col-sm-8 col-md-8 col-lg-8 util_d-flex util_gap-4px">
                                <span class="row-data__value" style="width: 100%;">
                                    <span class="row-data__colon">:</span>
                                    {{ $data['value'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @if (!$loop->last)
                        <div class="hr-intext">
                            <span class="hr-intext__line"></span>
                        </div>
                    @endif
                @endforeach

                <x-slot:footer>
                    <div class="grid cols-1 cols-sm-2">
                        <x-core::button variant="outline" data-dismiss="modal">
                            Edit Proposal
                        </x-core::button>

                        <x-core::button variant="primary" wire:click="save">
                            Ajukan Proposal
                        </x-core::button>
                    </div>
                </x-slot:footer>
            </x-core::modal.body>
        </x-core::form>
    </x-core::modal>
</x-core::livewire.layouts.create-edit>
@pushonce('scriptsVendor')
    @script
        <script>
            document.addEventListener('input', (e) => {
                const target = e.target;
                if (target.getAttribute('format-currency') === 'format-currency') {
                    target.addEventListener('keyup', (e) => {
                        let value = target.value;
                        value = value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        target.value = value;
                    });
                }
            });

            document.addEventListener('livewire:initialized', () => {
                let wysiwygElms = document.querySelectorAll(".text-editor");

                wysiwygElms.forEach((el) => {
                    initWysiwyg(el);
                });

                Livewire.hook("morph.updated", ({
                    el
                }) => {
                    if (typeof el.className === 'string' && el.className.includes('text-editor')) {
                        setTimeout(() => {
                            initWysiwyg(el);
                        }, 250);
                    }
                });

                Livewire.hook("element.init", ({
                    el
                }) => {
                    if (typeof el.className === 'string' && el.className.includes('text-editor')) {
                        setTimeout(() => {
                            initWysiwyg(el);
                        }, 250);
                    }

                    if (el.tagName === 'INPUT' && el.getAttribute('format-currency') === 'format-currency') {
                        el.type = 'text';

                        setTimeout(() => {
                            let value = el.value;
                            value = value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            el.value = value;
                        }, 1);
                    }
                });

                const initWysiwyg = (el) => {
                    let dataName = el.dataset.name;
                    const qnQuillToolbarOptions = [
                        [{
                            size: ["small", false, "large", "huge"]
                        }], // custom dropdown
                        // [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

                        ["bold", "italic", "underline"], // toggled buttons

                        [{
                            list: "ordered"
                        }, {
                            list: "bullet"
                        }, {
                            align: []
                        }],

                        ["link"],
                    ];

                    // Jika file js ini dipanggil otomatis semua textare dengan class .text-editor akan diubah menjadi text rich
                    var quill = new Quill(el, {
                        modules: {
                            toolbar: qnQuillToolbarOptions,
                        },
                        theme: "snow",
                    });

                    // get class ql-editor inside el
                    let qlEditor = el.querySelector(".ql-editor");
                    document.querySelector(`input[name="${dataName}"]`).value = qlEditor.innerHTML;

                    // handle text change
                    quill.on("text-change", function() {
                        let value = quill.root.innerHTML;
                        let input = document.querySelector(`input[name="${dataName}"]`);
                        input.value = value;
                        $wire.record[dataName] = value;
                    });

                }

                // kembalikan oldCollapse ke tempatnya, berdasarkan .form-header btn[data-target="#collapse-{oldCollapse}"]
                oldCollapse.forEach((collapseTargetSelector) => {
                    let collapseToggle = document.querySelector('.form-header .btn[data-target="' +
                        collapseTargetSelector + '"]');
                    if (collapseToggle) {
                        collapseToggle.setAttribute("data-toggle", "collapse");
                    }
                });

                // saat ada aksi livewire dan after livewire terload, set searchable select
                Livewire.hook('element.init', ({
                    el
                }) => {
                    if (el.tagName == "SELECT" &&
                        (el.name == 'id_sumber_pendanaan' || el.name == 'id_bidang_ilmu' || el.name ==
                            'id_klaster_pendanaan' ||
                            el.name == 'id_tema_kegiatan')
                    ) {
                        // jika mengandung choices__input, maka skip
                        if (el.parentElement.classList.contains('choices__inner')) {
                            return;
                        }

                        new Choices(el, {
                            allowHTML: true,
                            shouldSort: false,
                            searchEnabled: true,
                            searchChoices: true,
                            searchFloor: 1,
                            searchResultLimit: 4,
                            searchFields: ['label', 'value'],
                        });
                    }

                    Livewire.on('confirm-save', () => {
                        setTimeout(() => {
                            document.getElementById("modal-confirm-save").classList.add(
                                "is-visible");
                        }, 250);
                    });
                });
                Livewire.hook('commit', ({
                    succeed
                }) => {
                    succeed(() => {
                        queueMicrotask(() => {
                            const userSudahMengajukanPendanaan = '{{ $forceCannotCreate ?? false }}';
                            if (userSudahMengajukanPendanaan) {
                                disabledActionButton();
                            }
                        });
                    });
                })
            });
        </script>
    @endscript
    <script>
        let oldCollapse = oldTableOfContent = [];

        function disabledActionButton() {
            document.querySelectorAll('button[wire\\:click="confirmSave"]').forEach((button) => {
                button.disabled = true;
            });
            document.querySelectorAll('button[wire\\:click="confirmSave"]').forEach((button) => {
                button.removeAttribute("wire:click");
            });

            // tambah juga utk wire:click="draft"
            document.querySelectorAll('button[wire\\:click="draft"]').forEach((button) => {
                button.disabled = true;
            });
            document.querySelectorAll('button[wire\\:click="draft"]').forEach((button) => {
                button.removeAttribute("wire:click");
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // handle user sudah mengajukan pendanaan (saat pertama kali halaman di load menggunakan js)
            const userSudahMengajukanPendanaan = '{{ $forceCannotCreate ?? false }}';
            if (userSudahMengajukanPendanaan) {
                disabledActionButton();
            }

            // saat pertama kali di load, set searchable select
            document.querySelectorAll(
                    'select[name="id_sumber_pendanaan"], select[name="id_bidang_ilmu"], select[name="id_klaster_pendanaan"], select[name="id_tema_kegiatan"]'
                )
                .forEach((select) => {
                    // jika mengandung choices__input
                    if (select.parentElement.classList.contains('choices__inner')) {
                        return;
                    }

                    // create new Choices
                    new Choices(select, {
                        allowHTML: true,
                        shouldSort: false,
                        searchEnabled: true,
                        searchChoices: true,
                        searchFloor: 1,
                        searchResultLimit: 4,
                        searchFields: ['label', 'value'],
                    });
                });

            // search .form-header data-toggle="collapse"
            document.querySelectorAll('.form-header .btn[data-toggle="collapse"]').forEach((collapseToggle) => {
                // get data-target
                let collapseTargetSelector = collapseToggle.dataset.target;

                // add to oldCollapse
                oldCollapse.push(collapseTargetSelector);

                // remove collapseToggle
                collapseToggle.removeAttribute("data-toggle");
            });
        });
    </script>
@endpushonce
