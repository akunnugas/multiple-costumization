@props([
    'data' => [],
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'backSubFooter' => null,
    'numberToc' => false,
    'withConfirmationModal' => false,
    'confirmationModalTitle' => null,
    'confirmationModalMessage' => null,
])
@php
    // default title
    if (empty($title)) {
        $title = (empty($resourceId) ? 'Tambah' : 'Edit') . ' ' . $resourceTitle;
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

    $defaultRouteName = $attributes['routeName'] ?? $routeName;
    $alert = $attributes['alert'] ?? $alert ?? null;
    [$module, $resource, $type] = explode('.', $defaultRouteName);

    if (!empty($backSubFooter)) {
        $action = url($backSubFooter);
    } else if (empty($resourceId)) {
        $action = route($module . '.' . $resource . '.' . 'index');
    } else {
        $action = route($module . '.' . $resource . '.' . 'show', [$resourceId]);
    }
@endphp

@pushOnce('head')
    <style>
        .full-page-loader {
            display: flex;
            position: fixed;
            left: 0;
            top: 0;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: #ffffff90;
            z-index: 9999;
        }

        .hidden {
            display: none !important;
        }
    </style>
@endPushOnce

<form wire:submit="save">
    <div class="form-nav">
        <div class="form-nav__left">
            <a href="{{ $action }}" class="btn btn_outline btn_sm">
                <span class="icon icon-arrow-left-mini"></span>
                <span class="btn__text">Kembali</span>
            </a>
        </div>
        <div class="form-nav__middle">
            <ul class="form-nav__breadcrumb">
                <li class="form-nav__breadcrumb-item">
                    <a href="#" class="form-nav__breadcrumb-btn">{{ $resourceTitle }}</a>
                </li>
                <li class="form-nav__breadcrumb-diagonal"></li>
                <li class="form-nav__breadcrumb-item active">
                    {{ empty($resourceId) ? 'Tambah' : 'Edit' }}
                </li>
            </ul>
        </div>
        <div class="form-nav__right">
            <div class="form-nav__wrapper">
                @if(empty($customAction))
                    @if ($withConfirmationModal)
                        <div class="form-nav__button-wrapper">
                            <button type="button" class="btn btn_primary btn_sm" data-toggle="modal"
                                data-target="#modal-confirm">
                                Simpan
                            </button>
                        </div>
                    @else
                        <div class="form-nav__button-wrapper">
                            <button wire:click="save" wire:loading.attr="disabled" wire:target="save" type="button" class="btn btn_primary btn_sm">
                                Simpan
                            </button>
                        </div>
                    @endif
                @else
                    {{ $customAction }}
                @endif
            </div>
        </div>
    </div>
    @if ($toc)
        <x-core::layouts.create.toc :data="$toc" :numberToc="$numberToc" />
    @endif
    <x-core::layouts.main.container :$menu :$title :$subtitle>
        <x-core::layouts.html.alert :data="$alert" />
        <div class="grid">
            @if (!isset($slot))
                <x-core::layouts.create.cards :$data />
            @else
                {{ $slot }}
            @endif
        </div>
    </x-core::layouts.main.container>
    @if (isset($attributes['routeName']))
        <input type="hidden" wire:model="routeName" value="{{ $defaultRouteName }}" />
    @endif

    <x-core::modal title="{{ $confirmationModalTitle ?? 'Konfirmasi Simpan Data' }}" variant="primary" id="modal-confirm">
        <x-core::modal.body>
            {{ $confirmationModalMessage ?? 'Apakah anda yakin ingin menyimpan data ini?' }}
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batal
                    </x-core::button>
                    <x-core::button class="confirm" variant="primary" wire:click="save">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::modal>

</form>

@pushOnce('end')
    <div class="full-page-loader hidden">
        <div class="loader">
            <span class="loader__spinner"></span>
        </div>
    </div>
@endPushOnce

@pushOnce('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-loading', () => {
                document.querySelector('.full-page-loader').classList.remove('hidden');
            });

            Livewire.on('hide-loading', () => {
                document.querySelector('.full-page-loader').classList.add('hidden');
            });

            Livewire.on('scroll-to-top', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            Livewire.on('scroll-to-section', (sectionId) => {
                const section = document.getElementById(sectionId);
                if (section) {
                    window.scrollTo({ top: section.offsetTop - 20, behavior: 'smooth' });
                }
            });

            Livewire.hook('commit', ({succeed}) => {
                succeed(() => {
                    queueMicrotask(() => {
                        // scroll to first input error
                        let firstError = document.querySelector('.form-control__helper.error');
                        if (firstError) {
                            // dicomment karena bug pada saat throw error validasi pada form surat tugas auditor
                            // window.scrollTo({ top: firstError.parentElement.offsetTop - 20, behavior: 'smooth' });
                            window.scrollTo({ top: firstError.getBoundingClientRect().top + window.scrollY - 150, behavior: 'smooth' });

                        }
                    });
                });
            });
        });
    </script>
@endPushOnce

{{-- Harus di blade dan (tidak menggunakan pushonce/bagian dari component) karena menggunakan $wire --}}
@script
    <script>
        const hookMultipleChoicesV2 = (el) => {
            const name = el.getAttribute("name");

            const choicesMultiple = new Choices(el, {
                allowHTML: true,
                shouldSort: false,
                searchEnabled: false,
                searchResultLimit: 20,
                delimiter: ",",
                editItems: true,
                removeItemButton: true,
                searchFields: ['label'],
                placeholder: true,
                searchPlaceholderValue: 'Cari...',
                loadingText: 'Memuat...',
                noResultsText: 'Tidak menemukan hasil yang cocok',
                noChoicesText: 'Tidak ada pilihan yang tersedia',
                uniqueItemText: 'Hanya satu item yang dapat dipilih',
                customAddItemText: 'Hanya hasil yang valid yang dapat ditambahkan',
                addItemText: (value) => {
                    return `Tekan Enter untuk menambahkan <b>"${value}"</b>`;
                },
            })

            choicesMultiple.passedElement.element.addEventListener('change', e => {
                // $wire agar tidak live terproses ketika change
                $wire.record[name] = [...e.target.selectedOptions].map(option => {
                    return option.value;
                });
            })
        }

        // utk keperluan styling agar width select multiple auto lebar (ngakalin)
        const stayMaxWith = (el) => {
            if (el.classList.contains("form-control__group") && el.querySelector('.wrapper-select-multiple-v2')) {
                // el.classList.remove('form-control__group');
                // jangan di remove, tapi cukup unset display saja
                el.style.display = 'unset';
            }
        }

        // saat livewire di inisialisasi
        document.addEventListener("livewire:initialized", () => {
            Livewire.hook("element.init", ({ el }) => {
                if (el.tagName == "SELECT") {
                    if (el.hasAttribute("multiple") && el.classList.contains("select-multiple-v2")) {
                        hookMultipleChoicesV2(el);
                    }
                }

                stayMaxWith(el);
            });

            Livewire.hook("morph.updated", ({ el }) => {
                stayMaxWith(el);
            });

            document.querySelectorAll("select[multiple].select-multiple-v2").forEach((el) => {
                hookMultipleChoicesV2(el);
            });
        });
    </script>
@endscript
