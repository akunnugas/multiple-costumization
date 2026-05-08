@foreach ($nodes as $row)
    <tr @if (!in_array($row->jenis_unit, [Modules\Core\Models\UnitKerja::STUDY_PROGRAM, Modules\Core\Models\UnitKerja::UNIT_NON_PRODI])) class="parent-row" style="background-color: #F8FAFC; cursor: pointer;" @endif
        data-id="{{ $row->id }}" data-parent-id="{{ $row->id_parent ?? 0 }}">
        <td>
            <p style="margin-left: {{ $row->info_level * 20 }}px; margin-bottom: 0; display: flex; align-items: center;">
                @if (!in_array($row->jenis_unit, [Modules\Core\Models\UnitKerja::STUDY_PROGRAM, Modules\Core\Models\UnitKerja::UNIT_NON_PRODI]))
                    <a href="#" class="toggle-children"
                        style="text-decoration: none; margin-right: 8px; color: #000000; width: 16px;">
                        <i class="icon icon-chevron-up"> </i>
                    </a>
                @else
                    <span style="width: 24px;"></span>
                @endif

                @if (!in_array($row->jenis_unit, [Modules\Core\Models\UnitKerja::STUDY_PROGRAM, Modules\Core\Models\UnitKerja::UNIT_NON_PRODI]))
                    <b style="color: #4B5565">{{ $row->nama_unit }}</b>
                @else
                    <span>{{ $row->nama_unit }}</span>
                @endif
            </p>
        </td>

        @foreach ($header as $item)
            @if ($item['field'] == 'nama_unit')
                @continue
            @endif

            @if ($item['field'] == 'status' && in_array($row->jenis_unit, [Modules\Core\Models\UnitKerja::STUDY_PROGRAM, Modules\Core\Models\UnitKerja::UNIT_NON_PRODI]))
                @if ($row->is_mapping)
                    <td>
                        <x-core::badge :variant="'success'" :type="'secondary'" :decoration="true">
                            Sudah Mapping
                        </x-core::badge>
                    </td>
                @else
                    <td>
                        <x-core::badge :variant="'warning'" :type="'secondary'" :decoration="true">
                            Belum Mapping
                        </x-core::badge>
                    </td>
                @endif
            @elseif ($item['field'] == 'status' && !empty($row->children))
                <td></td>
            @else
                <td>{{ $row->{$item['field']} ?? '' }}</td>
            @endif
        @endforeach

        <td>
            <div style="display: flex; align-items: center; gap: 4px;">
                <a href="{{ route('spmi.mapping-indikator-butir.show', [
                    'unit_id' => $row->id,
                    'period_id' => $row->id_audit_periode,
                ]) }}"
                    class="btn btn_outline btn_xs" style="">
                    <span class="icon icon-eye-solid"></span>
                </a>
                {{-- <x-core::button leading-icon="pencil-solid" variant="outline" size="xs" :href="route('spmi.mapping-indikator-butir.edit', [
                    'unit_id' => $row->id,
                    'period_id' => $row->id_audit_periode,
                    'jenis_edisi' => 'pr',
                ])" /> --}}
            </div>
        </td>
    </tr>

    @if (!empty($row->children))
        @include('spmi::pages.mapping-indikator-butir.partials.table-row', [
            'nodes' => $row->children,
            'header' => $header,
        ])
    @endif
@endforeach
