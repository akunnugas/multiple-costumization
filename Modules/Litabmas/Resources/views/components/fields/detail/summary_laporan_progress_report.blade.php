@php
    use Modules\Litabmas\Models\PengajuanPendanaanLaporanProgres;
@endphp
@if(!empty($item['original']))
    @php
        $data = $item['original'];
        $urutanReviewer = $item['urutan_reviewer'];
        $munculkanNamaReviewer = $item['munculkan_nama_reviewer'] ?? false;

        $header[] = ['field' => 'jenis_laporan_progres', 'label' => 'Nama Laporan',
            'options' => PengajuanPendanaanLaporanProgres::JENIS_LAP];
        if ($munculkanNamaReviewer) {
            foreach ($urutanReviewer as $reviewer) {
                $header[] = [
                    'field' => 'feedback_reviewer_' . $reviewer['reviewer_ke'],
                    'label' => 'Feedback Reviewer<br>' . $reviewer['nama'],
                    'isHtmlForLabel' => true
                ];
            }
        } else {
            foreach ($urutanReviewer as $i => $reviewer) {
                $header[] = [
                    'field' => 'feedback_reviewer_' . $reviewer['reviewer_ke'],
                    'label' => 'Reviewer Ke ' . ++$i
                ];
            }
        }
        $header[] = ['field' => 'id_dokumen_laporan_progres', 'label' => 'Dokumen yang Diupload',
            'component' => 'document_see_detail'];
        $header[] = ['field' => 'status_laporan_progres', 'label' => 'Status', 'component' => true];
    @endphp
    <div class="summary_laporan_progress_report">
        <x-core::table>
            <x-core::table.data :$header :$data :showNumber="true" />
        </x-core::table>
    </div>

    @pushonce('head')
        <style>
            .summary_laporan_progress_report .box-table__content {
                padding: unset;
                border-top: unset;
            }
            .summary_laporan_progress_report .box-table__footer {
                padding: unset;
            }
        </style>
    @endpushonce
@else
    -- Belum ada laporan progress report --
@endif

