@props([
    'title' => null,
    'icon' => null,
    'isDisableEdit' => false,
    'showCollapseInSection' => false,
    'pivotKlasterOutput' => null,
    'pivotKlasterOutcome' => null,
    'alertOutputOutcome' => [],
])

<x-core::layouts.create.card :$title :$icon :$showCollapseInSection>
    <x-core::layouts.html.alert :data="$alertOutputOutcome" />

    @php
        $staticAlertOutputAndOutcome = [
            'title' => 'Tentukan Luaran dan Publikasi',
            'message' => 'Silahkan pilih Luaran dan Publikasi yang diwajibkan, pilihan dapat mempengaruhi tanggal Batas Publikasi.',
            'type' => 'helper',
            'dismissible' => false,
        ];

        if ($pivotKlasterOutput->isEmpty() || $pivotKlasterOutcome->isEmpty()) {
            // jika kosong salah satu atau keduanya
            $url = route('litabmas.jenis-output-penelitian.index');
            if ($pivotKlasterOutput->isEmpty() && $pivotKlasterOutcome->isEmpty()) {
                $context = 'Luaran dan Publikasi';
            } elseif ($pivotKlasterOutput->isEmpty()) {
                $context = 'Luaran';
            } else {
                $context = 'Publikasi';
                $url = route('litabmas.jenis-outcome-penelitian.index');
            }

            $message =
                'Tidak ada ' .
                $context .
                ' yang tersedia, silakan tentukan terlebih dahulu
                <a href="' .
                $url .
                '" class="link" target="blank">di sini</a>.';
            $staticAlertOutputAndOutcome = [
                'message' => $message,
                'type' => 'warning',
                'dismissible' => false,
                'isHtml' => true,
            ];
        }
    @endphp
    <x-core::layouts.html.alert :data="$staticAlertOutputAndOutcome" />

    @if (!$pivotKlasterOutput->isEmpty())
        <div class="form-control">
            <label for="form-control-agenda-kegiatan" class="form-control__label" style="padding-bottom: 0;">
                Pilih Luaran yang wajib dipilih peneliti
                <span class="important">*</span>
            </label>
        </div>
        <x-core::table>
            <div class="box-table" wire:ignore>
                <div class="box-table__content">
                    <div class="table-max table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th class="cell-check cell-center">No</th>
                                    <th>Nama Luaran</th>
                                    <th class="cell-action cell-center">Apakah Wajib?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pivotKlasterOutput as $output)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $output->nama_output }}
                                        </td>
                                        <td class="cell-check cell-center">
                                            <x-core::checkbox.control class="check-item" value="{{ $output->id }}"
                                                checked="{{ $output->is_checked }}" name="jenis_output_penelitian[]" :disabled="$isDisableEdit"
                                                wire:model="record.jenis_output_penelitian.{{ $output->id }}" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-core::table>
    @endif

    @if (!$pivotKlasterOutcome->isEmpty())
        <div class="form-control">
            <label for="form-control-agenda-kegiatan" class="form-control__label" style="padding-bottom: 0;">
                Pilih Publikasi yang wajib dipilih peneliti
                <span class="important">*</span>
            </label>
        </div>
        <x-core::table>
            <div class="box-table" wire:ignore>
                <div class="box-table__content">
                    <div class="table-max table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th class="cell-check cell-center">No</th>
                                    <th>Nama Publikasi</th>
                                    <th>Batas Pengumpulan Publikasi
                                        <span data-tooltip="Batas pengumpulan ini diambil dari data Periode Pendanaan"
                                            data-placement="top">
                                            <span class="icon icon-information-circle-solid"></span>
                                        </span>
                                    </th>
                                    <th class="cell-action cell-center">Apakah Wajib?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pivotKlasterOutcome as $outcome)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $outcome->nama_outcome }}
                                        </td>
                                        <td id="outcome_limit_{{ $outcome->id }}"
                                            data-limit="{{ $outcome->penambahan_batas_pengumpulan_outcome }}">
                                            {{ $outcome->format_batas_pengumpulan_outcome }}
                                        </td>
                                        <td class="cell-check cell-center">
                                            <x-core::checkbox.control class="check-item" value="{{ $outcome->id }}"
                                                checked="{{ $outcome->is_checked }}" name="jenis_outcome_penelitian[]"
                                                onchange="setOutcomeLimit({{ $outcome->id }})" :disabled="$isDisableEdit"
                                                wire:model="record.jenis_outcome_penelitian.{{ $outcome->id }}" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-core::table>
    @endif

    <script>
        function setOutcomeLimit(id) {
            const limits = document.querySelectorAll('[id^="outcome_limit_"]');
            let limit = 0;
            let limitStr = '';

            limits.forEach(function(item) {
                const checkbox = item.closest('tr').querySelector('.check-item');
                if (checkbox.checked) {
                    const itemLimit = parseInt(item.getAttribute('data-limit'));
                    if (itemLimit > limit) {
                        limit = itemLimit;
                        limitStr = item.getAttribute('data-limit');
                    }
                }
            });

            const trOutcome = document.getElementById('div_tr_outcome');
            const tdOutcome = trOutcome.getElementsByTagName('td')[trOutcome.getElementsByTagName('td').length - 1];

            const input = tdOutcome.getElementsByTagName('input')[0];
            input.value = limitStr;

            Livewire.dispatch("updateAgendaTanggalSelesai", { tanggal: limitStr });
        }
    </script>
</x-core::layouts.create.card>
