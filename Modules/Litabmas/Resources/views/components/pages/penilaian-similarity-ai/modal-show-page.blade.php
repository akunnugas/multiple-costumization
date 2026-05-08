@php
    $fieldSimilarity = [
        ['field' => 'penilaian_index_similarity', 'required' => true, 'label' => 'Berikan Feedback',
            'placeholder' => 'Contoh Nilai Similarity: 20%', 'type' => 'number', 'data-number-max' => 100,
            'data-number-float' => true
        ],
        ['field' => 'id_dokumen_penilaian_similarity', 'label' => 'Upload Dokumen Hasil Similarity',
            'file_type' => ['pdf'], 'max_size' => (1024 * 5)
        ],
        ['field' => 'key', 'value' => 'similarity', 'type' => 'hidden']
    ];

    $fieldAI = [
        ['field' => 'penilaian_index_ai', 'required' => true, 'label' => 'Berikan Feedback',
            'placeholder' => 'Contoh Nilai AI: 20%', 'type' => 'number', 'data-number-max' => 100,
            'data-number-float' => true
        ],
        ['field' => 'id_dokumen_penilaian_ai', 'label' => 'Upload Dokumen Hasil Artificial Intelligence',
            'file_type' => ['pdf'], 'max_size' => (1024 * 5)
        ],
        ['field' => 'key', 'value' => 'ai', 'type' => 'hidden']
    ];
@endphp

<x-core::modal title="Berikan Penilaian:" variant="primary" id="modal-penilaian-similarity">
    <x-core::form method="PUT">
        <x-core::modal.body>
            <div class="modal__content-input">
                @foreach ($fieldSimilarity as $item)
                    @php
                        $item['name'] ??= $item['field'];
                        unset($item['field']);

                        $attributes = Page::buildAttributes($item);
                    @endphp
                    <x-core::controls.form {{ $attributes }} />
                @endforeach
            </div>
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>
                    <x-core::button variant="primary" type="submit">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>

<x-core::modal title="Berikan Penilaian:" variant="primary" id="modal-penilaian-ai">
    <x-core::form method="PUT">
        <x-core::modal.body>
            <div class="modal__content-input">
                @foreach ($fieldAI as $item)
                    @php
                        $item['name'] ??= $item['field'];
                        unset($item['field']);

                        $attributes = Page::buildAttributes($item);
                    @endphp
                    <x-core::controls.form {{ $attributes }} />
                @endforeach
            </div>
            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>
                    <x-core::button variant="primary" type="submit">
                        Simpan
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>

@pushonce('scripts')
    <script>
        function uploadDokumen(idAspek) {
            let idModal = null;
            let route = null;
            let title = 'Berikan Penilaian:';
            let nilai = document.querySelector(`[data-nilai-${idAspek}]`).dataset;
            if (idAspek === 'similarity') {
                title += ' Similarity Proposal';
                idModal = 'modal-penilaian-similarity';
                route = `{{ route('litabmas.penilaian-administrasi.penilaian-similarity', $resourceId) }}`;
                nilai = nilai.nilaiSimilarity;
            } else {
                title += ' Artificial Intelligence';
                idModal = 'modal-penilaian-ai';
                route = `{{ route('litabmas.penilaian-administrasi.penilaian-ai', $resourceId) }}`;
                nilai = nilai.nilaiAi;
            }

            // set modal__title 'Berikan Penilaian: Similarity Proposal'
            const modal = document.getElementById(idModal);
            modal.querySelector('form').action = route;
            modal.querySelector('.modal__title').textContent = title;
            modal.querySelector(`[name=penilaian_index_${idAspek}]`).value = nilai;

            modal.classList.add('is-visible');
        }
    </script>
@endpushonce
