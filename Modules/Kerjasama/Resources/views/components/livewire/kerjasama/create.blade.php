@props([
    'data' => [],
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'backSubFooter' => null,
    'numberToc' => false,
])
@php
    // default title
    $actionLabel = (empty($resourceId) ? 'Tambah' : 'Edit');
    if (empty($title)) {
        $title = $actionLabel . ' ' . $resourceTitle;
    }

    // parameter form
    if (empty($resourceId)) {
        $method = 'POST';
        $action = Page::indexURL();
    } else {
        $method = 'PUT';
        $action = Page::detailURL($resourceId);
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

        foreach ($dataPihakPenanggungJawab as $i => $section) {
            if (empty($section['id'])) {
                $section['id'] = Str::kebab($section['title']);
            }

            $toc[$section['id']] = $section['title'];
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

<div class="">
    <x-core::quantum-3.layouts.main.container :$menu :$title :$subtitle class="row" :withHeader="false">
        <x-core::quantum-3.layouts.html.alert style="margin-bottom:1rem" />

        <header class="qn-header z-2 sticky-top p-md-3 py-2 py-md-2 border-bottom shadow-sm bg-white">
            <div class="container-fluid d-grid d-flex justify-content-between align-items-center position-relative">
                <a href="{{ $action }}" class="btn btn-light gap-2 d-none d-md-flex" role="button">
                    <i class="sym sym-arrow-narrow-left"></i>
                    Kembali
                </a>
                <a href="{{ $action }}" class="btn btn-icon btn-light d-block d-md-none" role="button">
                    <i class="sym sym-arrow-narrow-left"></i>
                </a>
                <nav class="position-absolute top-50 start-50 translate-middle" aria-label="breadcrumb">
                    <ol class="qn-form-breadcrumb breadcrumb m-0 d-none d-md-flex">
                        <li class="breadcrumb-item">
                            <a href="{{ $action }}">{{ $resourceTitle }}</a>
                        </li>
                        <li class="breadcrumb-item active fw-bold" aria-current="page">
                            {{ $actionLabel }}
                        </li>
                    </ol>
                    <ol class="breadcrumb m-0 d-flex d-md-none">
                        <li class="breadcrumb-item active fw-bold" aria-current="page">
                            {{ $title }}
                        </li>
                    </ol>
                </nav>
                <div class="d-flex gap-3 align-items-center">
                    @if (!empty($resourceId))                            
                        {{-- <span class="d-none d-xl-flex gap-1 text-body-tertiary">
                            <i class="sym sym-check"></i>
                            Tersimpan 2 menit yang lalu
                        </span> --}}
                        <!-- <span
                            class="d-flex d-xl-none gap-1 text-body-tertiary"
                            data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            data-bs-title="Tersimpan 2 menit yang lalu"
                        >
                        <i class="sym sym-check"></i> -->
                    @endif
                </span>
                    <button
                        wire:loading.attr="disabled"                        
                        wire:click="save"
                        class="d-none d-md-block btn btn-primary"
                    >
                        Simpan
                    </button>
                </div>
            </div>
        </header>

        @if ($toc)
            <x-core::quantum-3.layouts.create.toc :data="$toc" />
        @endif

        <form wire:submit="save" class="qn-form needs-validation py-3 ps-3 position-relative col-xl-6 {{ $toc ? 'col-md-9 offset-xxl-1' : 'col-xl-6 offset-xl-3' }}" novalidate>
            <div class="row px-3 row-cols-1 gy-4">
                <div class="d-md-block d-none">
                    <x-core::quantum-3.layouts.main.header :$menu :$title :$subtitle />
                </div>

                @foreach ($data as $sectionId => $section)
                    @php
                        $section['id'] = $sectionId;
                        $section['data'] = $section['items'];
                        $section = Arr::only($section, ['title', 'subtitle', 'data', 'icon', 'id']);
                        $attributes = Page::buildAttributes($section);
                    @endphp
                    <x-core::quantum-3.layouts.create.card :$sectionId {{ $attributes }} :$showCollapseInSection />
                @endforeach

                @foreach ($dataPihakPenanggungJawab as $sectionId => $section)
                    @php
                        $section['id'] = $sectionId;
                        $section['data'] = $section['items'];
                        $section = Arr::only($section, ['title', 'subtitle', 'data', 'icon', 'id']);
                        $attributes = Page::buildAttributes($section);
                        $keyPihak = $loop->iteration;
                    @endphp
                    <x-core::quantum-3.layouts.create.card :$sectionId {{ $attributes }} :$showCollapseInSection>
                        @foreach ($section['data'] as $item)
                            @php
                                $item['name'] ??= $item['field'] ?? '';
                                unset($item['field']);
    
                                $attributes = Page::buildAttributes($item);
                            @endphp
                            @if (!empty($item['separator']))
                                <div class="form-group col-md-12 d-flex align-items-center">
                                    <hr class="w-100">
                                    @if (!empty($item['label']))
                                        <span class="text-nowrap px-3">{{ $item['label'] }}</span>
                                        <hr class="w-100">
                                    @endif
                                </div>
                            @else                            
                                <x-core::quantum-3.controls.form {{ $attributes }} />
                            @endif
                        @endforeach
                        @if (!empty($dataPenanggungJawab[$keyPihak]))
                            @foreach ($dataPenanggungJawab[$loop->iteration] as $keyPenanggungJawab => $itemPenanggungJawab)
                                <div class="col-md-12 ">
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center pb-3">
                                        <span class="fw-semibold fs-5">Penanggung Jawab {{ $loop->iteration }}</span>
                                        <div class="d-flex gap-1">
                                            @if (!empty($pihakMitraIndex) && $keyPihak == $pihakMitraIndex)                                        
                                                <x-core::quantum-3.button 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modal-select-kontak" 
                                                    variant="outline-primary" 
                                                    leadingIcon="users"
                                                    :data-keyPihak="$keyPihak"
                                                    :data-keyPenanggungJawab="$keyPenanggungJawab"
                                                >
                                                    Ambil Dari Kontak
                                                </x-core::quantum-3.button>
                                            @endif
                                            @if (!$loop->first)
                                                <x-core::quantum-3.button wire:click='deletePenanggungJawab({{ $keyPihak }},{{ $keyPenanggungJawab }})' variant="danger" leadingIcon="trash">Hapus</x-core::quantum-3.button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row row-cols-1 row-cols-md-3 g-3">
                                        @foreach ($itemPenanggungJawab as $item)                                
                                            @php
                                                $item['name'] ??= $item['field'] ?? '';
                                                unset($item['field']);
            
                                                $attributes = Page::buildAttributes($item);
                                            @endphp
                                            @if (!empty($item['separator']))
                                                <div class="form-group col-md-12 d-flex align-items-center">
                                                    <hr class="w-100">
                                                    @if (!empty($item['label']))
                                                        <span class="text-nowrap px-3">{{ $item['label'] }}</span>
                                                        <hr class="w-100">
                                                    @endif
                                                </div>
                                            @else                            
                                                <x-core::quantum-3.controls.form {{ $attributes }} />
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-md-12 text-center text-muted py-3">
                                <em>Belum ada Penanggung Jawab untuk pihak ini.</em>
                            </div>
                        @endif
                        <div class="col-md-12 mt-3 d-flex justify-content-center">
                            <x-core::quantum-3.button variant="light" leadingIcon="plus" wire:click='addPenanggungJawab({{ $keyPihak }})'>
                                Tambah Penanggung Jawab Pihak ke {{ $keyPihak }}
                            </x-core::quantum-3.button>
                        </div>
                    </x-core::quantum-3.layouts.create.card>

                @endforeach

            </div>
        </form>
    </x-core::quantum-3.layouts.main.container>

    <div class="d-block d-md-none rounded-top-4 shadow-lg sticky-bottom">
        @if ($toc)
            <x-core::quantum-3.layouts.create.toc-mobile :data="$toc"/>
        @endif
        <div class="w-100 d-flex bg-white gap-2 p-3">
            @if ($toc)
                <button type="button" class="btn btn-icon btn-light" aria-label="Table of Content" data-bs-toggle="collapse" data-bs-target="#tocMobile" aria-expanded="false" aria-controls="tocMobile">
                    <i class="sym sym-list"></i>
                </button>
            @endif
            <x-core::quantum-3.button
                wire:loading.attr="disabled"                         
                wire:click='save' 
                
                class="w-100" 
                color="primary"
            >
                Simpan
            </x-core::quantum-3.button>
        </div>
    </div>

    @pushOnce('end')
        <div class="full-page-loader hidden">
            <div class="spinner-border text-primary align-self-center" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    @endPushOnce

    <x-core::quantum-3.modal.select-kontak 
        id="modal-select-kontak" 
        modalDialogClass="modal-dialog-centered"
        :kontak="$this->kontakMitra"
    />
    
    {{-- Harus di blade dan (tidak menggunakan pushonce/bagian dari component) karena menggunakan $wire --}}
    @script
        <script>    
            const initSelect2 = (el) => {
                const name = el.getAttribute("name");

                let select2Element = $(el).select2({
                    theme: 'quantum3',
                });

                let model
                if (el.hasAttribute('wire:model.change')) {
                    model = el.getAttribute('wire:model.change')
                } else {
                    model = el.getAttribute('wire:model')
                }

                // handle select2 event
                $(el).on('select2:select', function (e) {
                    let value = $(el).val()
                    if (el.hasAttribute('wire:model.change')) {
                        let model = el.getAttribute('wire:model.change')
                        @this.set(model, value)
                    } else {
                        let model = el.getAttribute('wire:model')
                        $wire.set(model, value)
                    }
                });
            }
            
            document.addEventListener("livewire:initialized", () => {
                document.querySelectorAll("select.select-search, select.select-tree").forEach((el) => {
                    initSelect2(el);
                });

                Livewire.on('show-loading', () => {
                    document.querySelector('.full-page-loader').classList.remove('hidden');
                });

                Livewire.on('hide-loading', () => {
                    document.querySelector('.full-page-loader').classList.add('hidden');
                });

                Livewire.hook("morph.updated", ({ el }) => {
                    if (el.tagName == "SELECT") {
                        if (el.classList.contains("select-search") || el.classList.contains("select-tree")) {
                            initSelect2(el);
                        }
                    }
                });

                Livewire.on('scroll-to-element',(data) => {
                    const id = data[0].id
                    setTimeout(() => {                        
    
                        if (!id) {
                            return;
                        }
    
                        const el = window.document.getElementById(id);
                        if (!el) {
                            return;
                        }
    
                        try {
                      el.scrollIntoView({
                                behavior: 'smooth',
                            });
                        } catch {}
                    });
                })
            });

            function handleSelectKontak(ev) {
                const targetElement = ev.target;
                const modalElement = document.getElementById('modal-select-kontak');
                const keyPihak = modalElement.getAttribute('data-selected-keypihak');
                const keyPenanggungJawab = modalElement.getAttribute('data-selected-keypenanggungjawab');
                
                bootstrap.Modal.getInstance(modalElement).hide();
                @this.fillPenanggungJawabFromSelectKontak(
                    targetElement.getAttribute('data-kontakid'),
                    keyPihak,
                    keyPenanggungJawab
                );
            }

            document.getElementById('modal-select-kontak').addEventListener('show.bs.modal', (e) => {
                const buttonElement = e.relatedTarget;
                const keyPihak = buttonElement.getAttribute('data-keypihak');
                const keyPenanggungJawab = buttonElement.getAttribute('data-keypenanggungjawab');
                
                const modalElement = document.getElementById('modal-select-kontak');
                modalElement.setAttribute('data-selected-keypihak', keyPihak)
                modalElement.setAttribute('data-selected-keypenanggungjawab', keyPenanggungJawab)

                modalElement.querySelectorAll('.box-switch').forEach(element => {                    
                    element.addEventListener('click', handleSelectKontak)
                });
            })

            document.addEventListener('input', function(e) {
                if (e.target.type === 'number' && e.target.name?.includes('telepon')) {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.target.type === 'number' && e.target.name?.includes('telepon')) {
                    if (['e','E','+','-','.'].includes(e.key)) {
                        e.preventDefault();
                    }
                }
            });

        </script>
    @endscript
</div>

