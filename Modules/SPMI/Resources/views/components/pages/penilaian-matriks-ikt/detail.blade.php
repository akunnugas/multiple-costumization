<div class="card__header util_mt-40" style="padding: 0px 0px 15px !important;">
    <div class="card__header-left">
        <h3>Daftar Skor</h3>
    </div>
    <div class="card__header-right">
        {{-- FIXME: Belum fungsional --}}
        {{-- <a href="http://localhost:8001/spmi/penilaian-matriks/1/edit" class="btn btn_primary" style="">
            <span class="icon icon-plus square-solid"></span>
            Tambah Skor
        </a> --}}
    </div>
</div>
@php
    $options = $data['scoreOptions'];
    $data = $data['PenilaianMatriksScores'];
    
    $header = [
        [
            'field' => 'nilai',
            'label' => 'Skor',
            'options' => $options,
        ],
        [
            'field' => 'deskripsi',
            'component' => true,
        ],
        [
            'field' => 'apakah_nonaktif',
            'label' => 'Sembunyikan Skor',
            'type' => 'switch',
            'component' => true,
        ],
        [
            'field' => 'kriteria',
            'label' => 'Kriteria',
            'maxlength' => 255,
        ],
        [
            'field' => 'rumus_penilaian',
            'maxlength' => 100,
        ],
    ];
@endphp
<x-core::table.data :$header :$data />
<br />
