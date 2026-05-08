@if(!empty($item['original']))
    @php
        $data = $item['original'];

        $header = [
            ['field' => 'tanggal_aktivitas_penelitian', 'label' => 'Tanggal Aktivitas', 'component' => 'date'],
            ['field' => 'nama_aktivitas_penelitian', 'label' => 'Nama Aktivitas', 'definer' => true],
            ['field' => 'lokasi_aktivitas_penelitian', 'label' => 'Tempat Aktivitas'],
            ['field' => 'id_dokumen_logbook', 'label' => 'File Pendukung', 'component' => 'document_see_detail'],
            ['field' => 'feedback_logbook', 'label' => 'Feedback Pembimbing'],
            ['field' => 'id_dokumen_feedback_logbook', 'label' => 'File Feedback', 'component' => 'document_see_detail'],
        ];
    @endphp
    <div class="summary_aktivitas_penelitian">
        <x-core::table>
            <x-core::table.data :$header :$data :showNumber="true" />
        </x-core::table>
    </div>

    @pushonce('head')
        <style>
            .summary_aktivitas_penelitian .box-table__content {
                padding: unset;
                border-top: unset;
            }
            .summary_aktivitas_penelitian .box-table__footer {
                padding: unset;
            }
        </style>
    @endpushonce
@else
    -- Belum ada aktivitas penelitian --
@endif

