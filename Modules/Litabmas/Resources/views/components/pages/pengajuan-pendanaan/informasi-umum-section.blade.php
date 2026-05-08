@props([
    'title' => null,
    'icon' => null,
    'showCollapseInSection' => false,
    'fields' => [],
    'idKlasterPendanaan' => null,
    'requiredOutputs' => [],
    'oldRecordOutput' => [],
    'headerInfoKlaster' => [],
])

@pushonce('head')
    <style>
        .form-control .form-control__label-checkbox.label-checkbox_disabled {
            display: flex;
            column-gap: 12px;
        }

        .alert_klaster {
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 0.063rem solid;
            display: flex;
            font-size: 0.875rem;
            line-height: 1.25rem;
            border-color: var(--qn-primary-400);
            background: var(--qn-primary-100);
        }
    </style>
@endpushonce
{{-- @dd($headerInfoKlaster) --}}
<x-core::layouts.create.card :$showCollapseInSection :$title :$icon>

    @include('litabmas::components.pages.pengajuan-pendanaan.header.header-pengajuan')

    @foreach ($fields as $item)
        @php
            $item['name'] ??= $item['field'];
            unset($item['field']);

            $attributes = Page::buildAttributes($item);
        @endphp
        <x-core::controls.form {{ $attributes }} />
    @endforeach

    {{-- [Start] Custom Field --}}
    <hr class="dashed" />
    @if (!empty($idKlasterPendanaan))
        @php
            $jenisOutputPenelitianOptDefault = \Modules\Litabmas\Models\JenisOutputPenelitian::options(); // return array yg isinya obj
            $requiredOutputs = json_decode(json_encode($requiredOutputs), true); // jadikan array karena sebelumnya object
            $requiredOutputs = array_column($requiredOutputs, 'id_jenis_output_penelitian', 'id_jenis_output_penelitian');

            $jenisOutputPenelitianOpt = [];
            foreach ($jenisOutputPenelitianOptDefault as $key => $value) {
                if (isset($requiredOutputs[$key])) {
                    $jenisOutputPenelitianOpt[$key] =
                        $value . ' <span class="badge badge_secondary-success badge_sm util_pl-4">Wajib</span>';
                    continue;
                } else {
                    $jenisOutputPenelitianOpt[$key] = $value;
                }
            }

            $required = [];
            foreach ($requiredOutputs as $key => $value) {
                $required[$key] = $key;
            }
            $valueChecked = empty($oldRecordOutput) ? $required : $oldRecordOutput;

            $item = [
                'field' => 'jenis_output_penelitian',
                'label' => 'Luaran Kegiatan',
                'required' => true,
                'control' => 'checkbox',
                'options' => $jenisOutputPenelitianOpt,
                'disabled' => array_keys($required),
                'value' => $valueChecked,
                'isValueHtml' => true,
                'wire:model' => 'record.jenis_output_penelitian',
            ];
            $item['name'] ??= $item['field'];
            unset($item['field']);

            $attributes = Page::buildAttributes($item);
        @endphp
    @else
        @php
            $jenisOutputPenelitianOptDefault = \Modules\Litabmas\Models\JenisOutputPenelitian::options();
            $item = [
                'field' => 'jenis_output_penelitian',
                'label' => 'Luaran Kegiatan',
                'required' => true,
                'control' => 'checkbox',
                'options' => $jenisOutputPenelitianOptDefault,
                'isValueHtml' => false,
                'wire:model' => 'record.jenis_output_penelitian',
            ];
            $item['name'] ??= $item['field'];
            unset($item['field']);

            $attributes = Page::buildAttributes($item);
        @endphp
    @endif
    <x-core::controls.form {{ $attributes }} />
    {{-- [End] Custom Field --}}

</x-core::layouts.create.card>
