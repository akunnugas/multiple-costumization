@props([
    'title' => null,
    'icon' => null,
    'showCollapseInSection' => false,
    'pivotKlasterAgenda' => null,
    'alertAgendaKegiatan' => [],
    'selectedOlderOutcome' => null
])

@pushonce('head')
    <style>
        tbody .important {
            color: var(--qn-danger);
            padding-right: 0.25rem;
        }
    </style>
@endpushonce

<x-core::layouts.create.card :$title :$icon :$showCollapseInSection>
    <x-core::layouts.html.alert :data="$alertAgendaKegiatan"/>

    @php
        if (!empty($pivotKlasterAgenda)) {
            $staticAlertAgenda = [
                'type' => 'helper',
                'message' => 'Jadwal Tahapan Kegiatan dibawah ini sesuai dengan yang telah ditentukan pada Sumber Pendanaan.',
                'dismissible' => false,
            ];
        } else {
            $staticAlertAgenda = [
                'message' => 'Pilih sumber pendanaan terlebih dahulu untuk menentukan Tahapan Kegiatan.',
                'type' => 'warning',
                'dismissible' => false,
            ];
        }
    @endphp
    <x-core::layouts.html.alert :data="$staticAlertAgenda"/>

    @if(!empty($pivotKlasterAgenda))
        <div class="form-control">
            <label for="form-control-agenda-kegiatan" class="form-control__label"
                   style="padding-bottom: 0;">
                Tentukan tanggal Tahapan Kegiatan pada klaster pendanaan ini
                <span class="important">*</span>
            </label>
        </div>
        <x-core::table>
            <div class="box-table">
                <div class="box-table__content">
                    <div class="table-responsive">
                        <table>
                            <thead>
                            <tr>
                                <th class="cell-check cell-center">No</th>
                                <th>{{ __('litabmas::klaster_pendanaan.agenda.nama') }}</th>
                                <th>{{ __('litabmas::klaster_pendanaan.agenda.waktu_mulai') }}</th>
                                <th>{{ __('litabmas::klaster_pendanaan.agenda.waktu_selesai') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $listAllowIntersect = \Modules\Litabmas\Models\AgendaKegiatan::ALLOWED_INTERSECT_STEPS;
                            @endphp
                            @foreach($pivotKlasterAgenda as $agenda)
                                @php
                                    $isAllowIntersect = in_array($agenda->kode_agenda, $listAllowIntersect);
                                    $isHasError = !empty($alertAgendaKegiatan['detailError'][$agenda->id_agenda_kegiatan]);
                                    $isHasErrorIntersect = $isHasError && str_contains($alertAgendaKegiatan['detailError'][$agenda->id_agenda_kegiatan], 'berpotongan');
                                    $isOutcome = $agenda->kode_agenda === \Modules\Litabmas\Models\AgendaKegiatan::STEP_PENGUMPULAN_HASIL;
                                @endphp
                                <tr id="{{ $isOutcome ? 'div_tr_outcome' : '' }}">
                                    <td>
                                        {{ $loop->iteration }}
                                    </td>
                                    <td>
                                        {{ $agenda->nama_agenda }}
                                        @if(!$isAllowIntersect)
                                            <span>*</span>
                                        @endif
                                        @if($isOutcome)
                                            <br>
                                            <span style="color: #697586;">
                                                (Tanggal Akhir <b>otomatis</b> dari batas pengumpulan publikasi terbesar, bisa diubah <b>manual</b>)
                                            </span>
                                        @endif
                                        @if($isHasError || $isHasErrorIntersect)
                                            <br>
                                            <span class="important">
                                                {{ $alertAgendaKegiatan['detailError'][$agenda->id_agenda_kegiatan] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $disabled = $agenda->hanya_ada_waktu_selesai || $isOutcome;

                                            $attributes = [
                                                'value' => $agenda->waktu_mulai ?? null,
                                                'type' => 'date',
                                                'defaultTypeDate' => true,
                                                'name' => 'agenda_kegiatan.' . $agenda->id_agenda_kegiatan . '.waktu_mulai',
                                                'wire:model' => 'record.agenda_kegiatan.' . $agenda->id_agenda_kegiatan . '.waktu_mulai',
                                                'disabled' => $disabled ? 'disabled' : null,
                                                'data-clear' => false,
                                                'data-cy' => 'tanggal_awal_'.$agenda->kode_agenda,
                                            ];
                                            $attributes = Page::buildAttributes($attributes);
                                        @endphp
                                        <x-core::controls.form {{ $attributes }} :show-label="false"
                                                               :show-helper="false"/>
                                    </td>
                                    <td>
                                        @php
                                            $disabled = $agenda->hanya_ada_waktu_mulai;
                                            $value = $agenda->waktu_selesai ?? null;
                                            if ($isOutcome) {
                                                $value = $placeholder = date('Y-m-d', strtotime($selectedOlderOutcome));
                                            }

                                            $attributes = [
                                                'value' => $value,
                                                'type' => 'date',
                                                'defaultTypeDate' => true,
                                                'name' => 'agenda_kegiatan.' . $agenda->id_agenda_kegiatan . '.waktu_selesai',
                                                'wire:model' => 'record.agenda_kegiatan.' . $agenda->id_agenda_kegiatan . '.waktu_selesai',
                                                'disabled' => $disabled ? 'disabled' : null,
                                                'data-clear' => false,
                                                'data-cy' => 'tanggal_akhir_'.$agenda->kode_agenda,
                                            ];
                                            $attributes = Page::buildAttributes($attributes);
                                        @endphp
                                        <x-core::controls.form {{ $attributes }} :show-label="false"
                                                               :show-helper="false"/>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="util_pt-4">
                <span class="util_mt-4">
                    <span class="important">*</span>Pengisian tanggal tidak boleh saling berpotongan dengan agenda lain.<br>
                </span>
            </div>
        </x-core::table>
    @endif

    <script>
        function attachTanggalAkhirListener() {
            const input = document.querySelector('input[data-cy="tanggal_akhir_pengumpulan_hasil"]');
            if (input && !input.hasAttribute('data-listener-attached')) {
                input.addEventListener('change', function(e) {
                    const tglEndPengumpulanHasil = e.target.value;
                    Livewire.dispatch('updateAgendaTanggalSelesai', { tanggal: tglEndPengumpulanHasil });
                });
                input.setAttribute('data-listener-attached', 'true');
            }
        }
        setInterval(attachTanggalAkhirListener, 500);
    </script>
</x-core::layouts.create.card>
