@props([
    'title' => null,
    'icon' => null,
    'showCollapseInSection' => false,
    'kodeJenisPendanaan' => null,
    'fields' => [],
    'alertIsianProposal' => [],
    'apakahAdaIsianProposal' => false,
    'headerInfoKlaster' => [],
])

<x-core::layouts.create.card :$title :$icon :$showCollapseInSection>
    <x-core::layouts.html.alert :data="$alertIsianProposal" />

    @include('litabmas::components.pages.pengajuan-pendanaan.header.header-pengajuan')

    {{-- Ketika belum memilih jenis pendanaan, atau ketika master isian proposal belum dibuat --}}
    @if (empty($kodeJenisPendanaan) || empty($apakahAdaIsianProposal))
        @php
            $message = empty($kodeJenisPendanaan)
                ? 'Pilih ' .
                    __('litabmas::pengajuan_pendanaan.kode_jenis_pendanaan') .
                    ' terlebih dahulu untuk menentukan ' .
                    $title .
                    '.'
                : 'Komponen Proposal belum ditentukan, silakan hubungi Administrator.';

            $staticAlertIsianProposal = [
                'message' => $message,
                'type' => 'warning',
                'dismissible' => false,
            ];
        @endphp
        <x-core::layouts.html.alert :data="$staticAlertIsianProposal" />
    @endif

    @if (!empty($kodeJenisPendanaan) && !empty($apakahAdaIsianProposal))
        @foreach ($fields as $item)
            @php
                // ini hidden semua hanya utk get name, value, dan wire
                if ($item['field'] == 'id_dokumen_proposal') {
                    continue;
                }

                $item['name'] ??= $item['field'];
                unset($item['field']);

                $attributes = Page::buildAttributes($item);
            @endphp
            <x-core::controls.form {{ $attributes }} />

            <div class="grid">
                <div class="col-12">
                    <div class="form-control">
                        <label class="form-control__label">
                            {{ $item['label'] }}<span class="important">*</span>
                        </label>

                        <textarea class="form-control__input textarea" wire:model.live="record.{{ $item['name'] }}" rows="4">{!! $item['value'] ?? $this->record[$item['name']] !!}</textarea>
                        @error($item['name'])
                            <div class="form-control__helper error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
        @endforeach

        @php
            $proposalDocument = array_filter($fields, function ($field) {
                return $field['field'] === 'id_dokumen_proposal';
            });
            $proposalDocument = $proposalDocument[0];

            $proposalDocument['name'] ??= $proposalDocument['field'];
            unset($proposalDocument['field']);

            $attributes = Page::buildAttributes($proposalDocument);
        @endphp
        <x-core::controls.form {{ $attributes }} />
    @endif
</x-core::layouts.create.card>
