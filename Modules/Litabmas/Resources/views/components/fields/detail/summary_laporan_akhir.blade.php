@if(!empty($item['original']))
    @php
        $data = $item['original'];

        $header = [
            ['field' => 'jenis_laporan_akhir', 'label' => 'Nama Laporan',
                'options' => \Modules\Litabmas\Models\PengajuanPendanaanLaporanAkhir::JENIS_LAPORAN_OPTIONS],
            ['field' => 'id_dokumen_laporan_akhir', 'label' => 'Dokumen yang Diupload',
                'component' => 'document_see_detail'],
        ];
    @endphp
    <div class="summary_laporan_akhir">
        <x-core::table>
            <x-core::table.data :$header :$data :showNumber="true" />
        </x-core::table>
    </div>

    @pushonce('head')
        <style>
            .summary_laporan_akhir .box-table__content {
                padding: unset;
                border-top: unset;
            }
            .summary_laporan_akhir .box-table__footer {
                padding: unset;
            }
        </style>
    @endpushonce
@else
    -- Belum ada laporan & keuangan --
@endif

