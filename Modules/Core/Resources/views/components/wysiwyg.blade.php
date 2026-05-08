@props([
    'value' => null,
])

@php
    $name = $attributes->get('name');
    $dataCy = $attributes->get('data-cy') ?? ($name ?? null);
@endphp
<div class="form-control" wire:ignore.self>
    <div class="text-editor" data-name="{{ $name }}" data-cy="{{ $dataCy }}">
        {!! html_entity_decode($value) !!}
    </div>
    <input type="hidden" name="{{ $name }}" />
</div>

@pushOnce('headVendor')
    <link href="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.snow.css') }}" rel="stylesheet">
    <script src="{{ Page::quantumAsset('js/vendors/quill-1.3.7/dist/quill.min.js') }}"></script>
@endPushOnce

{{-- Kalo Pake Livewire --}}
@if ($isLivewire ?? false)
    @script
        <script>
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

            // setTimeout(() => {
            //     let wysiwygElms = document.querySelectorAll(".text-editor");

            //     wysiwygElms.forEach((el) => {
            //         initWysiwyg(el);
            //     });
            // }, 250);

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
                });
            });
        </script>
    @endscript
@else
    {{-- Kalo Ngga Pake Livewire --}}
    <script>
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
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            let wysiwygElms = document.querySelectorAll(".text-editor");

            wysiwygElms.forEach((el) => {
                initWysiwyg(el);
            });
        });
    </script>
@endif
