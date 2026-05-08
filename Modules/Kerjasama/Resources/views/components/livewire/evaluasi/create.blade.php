@props([
    'data' => [],
    'menu' => [],
    'subtitle' => null,
    'title' => null,
    'backSubFooter' => null,
    'numberToc' => false,
    // Add these for edit support
    'resourceId' => null,
    'resourceTitle' => 'Evaluasi',
    'record' => [],
    'routeName' => null,
    'alert' => null,
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
    }

    $defaultRouteName = $attributes['routeName'] ?? $routeName;
    $alert = $attributes['alert'] ?? $alert ?? null;
    [$module, $resource, $type] = explode(
    '.', $defaultRouteName);


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

        <header class="qn-header z-3 sticky-top p-md-3 py-2 py-md-2 border-bottom shadow-sm bg-white">
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
            @if (!empty($resourceId))
                @method('PUT')
            @endif
            @csrf
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

                    @if ($sectionId == 'pertanyaan')
                        <x-core::quantum-3.layouts.create.card  :sectionId="$sectionId" {{ $attributes }} :$showCollapseInSection>
                            @foreach ($record['pertanyaan'] ?? [] as $index => $pertanyaan)
                                <div wire:key='pertanyaan-{{ $index }}' class="col-md-12 " id="{{ $pertanyaan['id'] ?? $index }}">
                                    @if (!$loop->first)
                                        <hr>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center pb-3">
                                        <span class="fw-semibold fs-5">Pertanyaan {{ $loop->iteration }}</span>
                                        <div class="d-flex gap-1">
                                            <x-core::quantum-3.button
                                                variant="primary"
                                                style="cursor: {{ $index === 0 ? 'not-allowed' : 'pointer' }}; "
                                                size="sm"
                                                data-bs-toggle="tooltip" title="Naik"
                                                leadingIcon="arrow-up"
                                                wire:click="movePertanyaanUp({{ $index }})"
                                                :disabled="$index === 0"
                                                title="Naik"
                                            />
                                            <x-core::quantum-3.button
                                                variant="primary"
                                                style="cursor: {{ $index === count($record['pertanyaan']) - 1 ? 'not-allowed' : 'pointer' }};"
                                                size="sm"
                                                data-bs-toggle="tooltip" title="Turun"
                                                leadingIcon="arrow-down"
                                                wire:click="movePertanyaanDown({{ $index }})"
                                                :disabled="$index === count($record['pertanyaan']) - 1"
                                                title="Turun"
                                            />
                                            <x-core::quantum-3.button
                                                variant="primary"
                                                size="sm"
                                                data-bs-toggle="tooltip" title="Duplikat"
                                                leadingIcon="copy"
                                                wire:click="duplicatePertanyaan({{ $index }})"
                                                title="Duplikat"
                                            />
                                           @php
                                                $hasAnswers = ($pertanyaan['jawaban_peserta_count'] ?? 0) > 0;
                                                $deleteTooltip = $hasAnswers ? 'Tidak dapat menghapus yang sudah ada jawaban' : 'Hapus';
                                            @endphp
                                                @if(!$hasAnswers)
                                                <x-core::quantum-3.button
                                                    variant="danger"
                                                    size="sm"
                                                    :disabled="$hasAnswers"
                                                    data-bs-toggle="tooltip" 
                                                    :title="$deleteTooltip"
                                                    leadingIcon="trash"
                                                    wire:click="removePertanyaan({{ $index }})"
                                                />
                                                @else
                                                <x-core::quantum-3.button
                                                    variant="danger"
                                                    size="sm"
                                                    style="cursor: not-allowed;"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $deleteTooltip }}"
                                                    leadingIcon="trash"
                                                />
                                                @endif
                                        </div>
                                    </div>
                                    <div class="row gy-2">
                                        {{-- <div class="col-12 col-md-2">
                                            <x-core::quantum-3.controls.form
                                                label="Nomor"
                                                name="record.pertanyaan.{{ $index }}.nomor"
                                                wire:model="record.pertanyaan.{{ $index }}.nomor"
                                                :required="true"
                                                type="text"
                                                :disabled="true"
                                            />
                                        </div> --}}
                                        <div class="col-12">
                                            <x-core::quantum-3.controls.form
                                                label="Pertanyaan"
                                                name="record.pertanyaan.{{ $index }}.pertanyaan"
                                                wire:model="record.pertanyaan.{{ $index }}.pertanyaan"
                                                :required="true"
                                                type="text"
                                            />
                                        </div>
                                        <div class="col-12">
                                            <x-core::quantum-3.controls.form
                                                label="Deskripsi"
                                                name="record.pertanyaan.{{ $index }}.deskripsi"
                                                wire:model="record.pertanyaan.{{ $index }}.deskripsi"
                                                type="textarea"
                                            />
                                        </div>
                                        <div class="col-12">
                                            <x-core::quantum-3.controls.form
                                                label="Tipe"
                                                name="record.pertanyaan.{{ $index }}.tipe"
                                                wire:model.live="record.pertanyaan.{{ $index }}.tipe"
                                                wire:change="$set('record.pertanyaan.{{ $index }}.tipe', $event.target.value)"
                                                type="select"
                                                :options="['option' => 'Pilihan', 'rating' => 'Rating', 'text' => 'Isian Teks']"
                                                :required="true"
                                                :disabled="$record['pertanyaan'][$index]['has_answers'] ?? false"
                                            />
                                        </div>
                                        @if (($record['pertanyaan'][$index]['tipe'] ?? '') === 'option')
                                            <div class="col-12">
                                                <label class="form-label">Opsi Jawaban</label>
                                                {{-- Show group error --}}
                                                @if ($errors->has("record.pertanyaan.$index.opsi_jawaban"))
                                                    <div class="text-danger small mb-2">
                                                        {{ $errors->first("record.pertanyaan.$index.opsi_jawaban") }}
                                                    </div>
                                                @endif
                                                @foreach ($record['pertanyaan'][$index]['opsi_jawaban'] ?? [] as $opsiIdx => $opsi)
                                                    <div class="input-group mb-2" wire:key="opsi-{{ $index }}-{{ $opsiIdx }}">
                                                        <input type="text"
                                                            class="form-control @if($errors->has("record.pertanyaan.$index.opsi_jawaban.$opsiIdx")) is-invalid @endif"
                                                            required
                                                            wire:model="record.pertanyaan.{{ $index }}.opsi_jawaban.{{ $opsiIdx }}{{ is_array($opsi) ? '.jawaban' : '' }}"
                                                            placeholder="Opsi jawaban">
                                                        <button class="btn btn-outline-danger"
                                                            type="button"
                                                            wire:click="removeOpsiJawaban({{ $index }}, {{ $opsiIdx }})">
                                                            <i class="sym sym-trash"></i>
                                                        </button>
                                                        @if($errors->has("record.pertanyaan.$index.opsi_jawaban.$opsiIdx"))
                                                            <div class="invalid-feedback d-block w-100">
                                                                {{ $errors->first("record.pertanyaan.$index.opsi_jawaban.$opsiIdx") }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                <x-core::quantum-3.button variant="primary" size="sm" leadingIcon="plus"
                                                    wire:click="addOpsiJawaban({{ $index }})">
                                                    Tambah Opsi Jawaban
                                                </x-core::quantum-3.button>
                                            </div>
                                        @elseif (($record['pertanyaan'][$index]['tipe'] ?? '') === 'rating')
                                            <div class="col-12">
                                                <x-core::quantum-3.controls.form
                                                    label="Rating Maksimal"
                                                    name="record.pertanyaan.{{ $index }}.max_rating"
                                                    wire:model.live="record.pertanyaan.{{ $index }}.max_rating"
                                                    type="number"
                                                    min="1"
                                                    :required="true"
                                                />
                                                <div class="mt-2">
                                                    @php
                                                        $max = (int)($record['pertanyaan'][$index]['max_rating'] ?? 5);
                                                    @endphp
                                                    <label class="form-label mb-2">Pratinjau Rating</label>
                                                    <div class="d-flex gap-2 text-warning fs-3">
                                                        @for ($r = 1; $r <= $max; $r++)
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor" width="1em" height="1em" style="vertical-align: -0.125em;">
                                                                <path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z" />
                                                            </svg>
                                                        @endfor
                                                    </div>
                                                    <div class="text-muted small mt-1">
                                                        (1 = Sangat Buruk, {{ $max }} = Sangat Baik)
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <x-core::quantum-3.controls.form
                                                :label="__('kerjasama::evaluasi_kuesioner.apakah_wajib')"
                                                name="record.pertanyaan.{{ $index }}.apakah_wajib"
                                                wire:model="record.pertanyaan.{{ $index }}.apakah_wajib"
                                                type="select"
                                                :options="['1' => 'Ya', '0' => 'Tidak']"
                                                :required="true"
                                            />
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="col-md-12 mt-3 d-flex justify-content-center">
                                <x-core::quantum-3.button variant="light" leadingIcon="plus" wire:click='addPertanyaan()'>
                                    Tambah Pertanyaan
                                </x-core::quantum-3.button>
                            </div>
                        </x-core::quantum-3.layouts.create.card>
                    @else
                        <x-core::quantum-3.layouts.create.card :sectionId="$sectionId" {{ $attributes }} :$showCollapseInSection />
                    @endif

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
            <x-core::quantum-3.button wire:click='save' class="w-100" color="primary">
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

            const initTooltips = () => {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    const existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            
            document.addEventListener("livewire:initialized", () => {
                // Initialize tooltip diawal
                initTooltips();

                Livewire.on('show-loading', () => {
                    document.querySelector('.full-page-loader').classList.remove('hidden');
                });

                Livewire.on('hide-loading', () => {
                    document.querySelector('.full-page-loader').classList.add('hidden');
                });

                document.querySelectorAll("select.select-search, select.select-tree").forEach((el) => {
                    initSelect2(el);
                });

                Livewire.hook("morph.updated", ({ el }) => {
                    if (el.tagName == "SELECT") {
                        if (el.classList.contains("select-search") || el.classList.contains("select-tree")) {
                            setTimeout(() => {
                                document.querySelectorAll("select.select-search:not(.select2-hidden-accessible), select.select-tree:not(.select2-hidden-accessible)").forEach((e) => {
                                    initSelect2(e);
                                });
                            });
                        }
                    }
                    // Re-initialize tooltips after Livewire updates
                    initTooltips();
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
                });
            });
        </script>
    @endscript
</div>

