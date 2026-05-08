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
@php
    $generalInformationSection = $data['informasi-umum'];
    $bidangIlmuDanTemaKegiatanSection = $data['bidang-ilmu-dan-tema-kegiatan'];
    $outputAndOutcomeSection = $data['output-dan-outcome'];
    $agendaKegiatanSection = $data['agenda-kegiatan'];

    if ($isDisableEdit) {
        foreach ($generalInformationSection['items'] as $key => $item) {
            $generalInformationSection['items'][$key]['disabled'] = true;
        }

        $alert = [
            'type' => 'warning',
            'message' => 'Anda tidak dapat mengubah data Klaster Pendanaan selain Tanggal Tahapan Kegiatan, karena sudah ada pengajuan proposal.'
        ];
    }
@endphp

{{-- @if ($isDisableEdit)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let collapseToggle = document.querySelectorAll('.form-header .btn[data-toggle="collapse"]');
            collapseToggle.forEach((c) => {
                if (c.dataset.target.includes('collapse-tahapan-kegiatan')) {
                    return;
                }
                c.click();
            });
        });
    </script>
@endif --}}

<x-core::livewire.layouts.create-edit :data="$data ?? []" :$routeName :$alert :numberToc="true">
    <x-slot:customAction>
        @if(!$statusPublikasi)
            <div class="form-nav__button-wrapper">
                <x-core::button wire:click="draft" variant="outline" type="button">
                    Simpan Draft
                </x-core::button>
            </div>
        @endif
        <div class="form-nav__button-wrapper">
            <x-core::button wire:click="save" type="button">
                Simpan Klaster
            </x-core::button>
        </div>
    </x-slot:customAction>

    {{-- [Start] Informasi Umum --}}
    <x-core::layouts.create.cards :data="[$generalInformationSection]" :$showCollapseInSection/>
    {{-- [End] Informasi Umum --}}

    {{-- [Start] Bidang Ilmu & Tema --}}
    <x-litabmas::pages.klaster-pendanaan.bidang-ilmu-tema-section :title="$bidangIlmuDanTemaKegiatanSection['title']"
                                                            :isDisableEdit="$isDisableEdit"
                                                            :icon="$bidangIlmuDanTemaKegiatanSection['icon']"
                                                            :fieldsBidangIlmuDanTemaKegiatan="$bidangIlmuDanTemaKegiatanSection['items']"
                                                            :$recordSavedBidangIlmuTemaKegiatan :$alertBidangIlmuTemaKegiatan :$usedBidangIlmuTemaKegiatan
                                                            :$showCollapseInSection :$state

    />
    {{-- [End] Bidang Ilmu & Tema --}}

    {{-- [Start] Output & Outcome --}}
    <x-litabmas::pages.klaster-pendanaan.output-outcome-section :title="$outputAndOutcomeSection['title']"
                                                                :isDisableEdit="$isDisableEdit"
                                                               :icon="$outputAndOutcomeSection['icon']"
                                                               :$showCollapseInSection :$alertOutputOutcome
                                                               :$pivotKlasterOutput :$pivotKlasterOutcome
    />
    {{-- [End] Output & Outcome --}}

    {{-- [Start] Tahapan Kegiatan --}}
    <x-litabmas::pages.klaster-pendanaan.agenda-kegiatan-section :title="$agendaKegiatanSection['title']"
                                                               :icon="$agendaKegiatanSection['icon']"
                                                               :$showCollapseInSection :$alertAgendaKegiatan
                                                               :$pivotKlasterAgenda :$selectedOlderOutcome
    />
    {{-- [End] Tahapan Kegiatan --}}
</x-core::livewire.layouts.create-edit>

@pushonce('scriptsVendor')
    <script>
        let oldCollapse = oldTableOfContent = [];

        document.addEventListener('livewire:initialized', () => {
            // kembalikan oldCollapse ke tempatnya, berdasarkan .form-header btn[data-target="#collapse-{oldCollapse}"]
            oldCollapse.forEach((collapseTargetSelector) => {
                let collapseToggle = document.querySelector('.form-header .btn[data-target="'+collapseTargetSelector+'"]');
                if (collapseToggle) {
                    collapseToggle.setAttribute("data-toggle", "collapse");
                }
            });

            // update view saat event 'update-date-end-outcome' di dispatch
            Livewire.on('update-date-end-outcome', (event) => {
                for (let key in event[0]) {
                    let value = event[0][key];

                    let el = document.querySelector('[id="outcome_limit_'+key+'"]');
                    if (el) {
                        el.innerHTML = value;
                    }
                }
            });

            // trigger touch event/input event where input contains class form-control__input_currency
            Livewire.on('render-nominal-input', (event) => {
                let inputCurrency = document.querySelectorAll('.form-control__input_currency');
                inputCurrency.forEach((input) => {
                    input.dispatchEvent(new Event('input', {
                        bubbles: true,
                        cancelable: true,
                    }));
                });
            });

            // saat ada aksi livewire dan after livewire terload, set searchable select
            Livewire.hook('element.init', ({el}) => {
                if (el.tagName == "SELECT" && (el.name == 'id_sumber_pendanaan' || el.name == 'bidang_ilmu')) {
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
            });

            Livewire.hook('commit', ({succeed}) => {
                // Simpan nilai sebelum di proses livewire

                // handle table of content top
                window.tableOfContentTop = document.querySelector('.form-table').style.top;

                // handle table of content active
                window.tableOfContentActiveElm = document.querySelector('.form-table .form-table__item.active');

                // handle breadcrumb/title
                window.formNavBreadcrumbOpacity = document.querySelector('.form-nav .form-nav__middle').style.opacity;

                // handle section active
                window.cardHighlightElm = document.querySelector('.container .card.card_form.card_highlight');

                // handle choices multi select yg tertutup setelah memilih satu
                // cari class .choices.is-focused.is-open yang memiliki attr data-type="select-multiple" dan aria-expanded="true"
                let choicesMultiSelectId = document.querySelector('.choices.is-focused.is-open[data-type="select-multiple"][aria-expanded="true"] select.select-multiple')?.id;
                if (choicesMultiSelectId) {
                    // window.choicesMultiSelectId = choicesMultiSelectId;
                }

                succeed(() => {
                    queueMicrotask(() => {
                        // Setel kembali nilai setelah di proses livewire
                        document.querySelector('.form-table').style.top = window.tableOfContentTop;
                        window.tableOfContentActiveElm.classList.add('active');
                        document.querySelector('.form-nav .form-nav__middle').style.opacity = window.formNavBreadcrumbOpacity;
                        window.cardHighlightElm.classList.add('card_highlight');

                        if (window.choicesMultiSelectId) {
                            let choicesMultiSelect = document.getElementById(window.choicesMultiSelectId);
                            if (choicesMultiSelect) {
                                let parent = choicesMultiSelect.parentElement;

                                // delay utk mendapatkan choices karena choices belum terbentuk
                                setTimeout(() => {
                                    let choices = parent.querySelector('.choices');
                                    if (choices) {
                                        choices.classList.add('is-focused');
                                        choices.classList.add('is-open');
                                        choices.setAttribute('aria-expanded', 'true');

                                        // search .choices__list.choices__list--dropdown inside window.choicesMultiSelect
                                        let choicesList = choices.querySelector('.choices__list.choices__list--dropdown');
                                        if (choicesList) {
                                            choicesList.classList.add('is-active');
                                            choicesList.setAttribute('aria-expanded', 'true');
                                        }
                                    }
                                }, 1);
                            }
                        }
                    });
                });
            })
        });

        document.addEventListener('DOMContentLoaded', () => {

            // saat pertama kali di load, set searchable select
            document.querySelectorAll('select[name="id_sumber_pendanaan"], select[name="bidang_ilmu"]').forEach((select) => {
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