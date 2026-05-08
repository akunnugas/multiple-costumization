@php
    $unit = $mitra =  "";
    foreach ($data as $id => $section) {
        if ($id == 'informasi-kerjasama') {
            foreach ($section['items'] as $item) {
                if ($item['field'] == 'id_unit_kerja') {
                    $unit = $item['text'];
                } else if ($item['field'] == 'id_mitra') {
                    $mitra = $item['text'];
                }
            }
        }
    }
    $dataMapping = [
        'judul_kegiatan',
        'id_induk_kerjasama',
        'id_mitra',
        'ruang_lingkup',
        'nomor_dokumen',
        'hasil_pelaksanaan',
        'link_dokumentasi'
    ];

    $dataRaw = array_merge(...array_column($data, 'items'));
    $dataMapped = [];
    foreach ($dataMapping as $key) {
        foreach ($dataRaw as $d) {
            if ($d['field'] == 'id_mitra') {
                $dataMapped['id_mitra'] = $rawData->indukKerjasama->mitra->nama_mitra;
                continue;
            }

            if ($d['field'] == $key) {
                $dataMapped[$key] = $d['text'] ?? '';
                continue;
            }
        }
    }

    $pihakUnit = $rawData->pihak_penanggung_jawab->where('model_pihak', 'Modules\Core\Models\UnitKerja')->first();
    $penanggungJawabUnit = $pihakUnit->penanggung_jawab[0];

    $pihakMitra = $rawData->pihak_penanggung_jawab->where('model_pihak', 'Modules\Kerjasama\Models\Mitra')->first();
    $penanggungJawabMitra = $pihakMitra->penanggung_jawab[0];
@endphp

<x-core::quantum-3.layouts.html>
    <div id="laporan-kerjasama" class="printable m-1">
        <div class="text-center mb-4">
            <h3>LAPORAN PELAKSANAAN KERJASAMA</h3>
        </div>
    
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th style="width: 10px">1.</th>
                    <th class="w-25">Judul Kegiatan</th>
                    <th style="width: 10px">:</th>
                    <td>{{ $dataMapped['judul_kegiatan'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>2.</th>
                    <th>Referensi Kerjasama (MoA/IA)</th>
                    <th style="width: 10px">:</th>
                    <td>{{ $dataMapped['id_induk_kerjasama'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>3.</th>
                    <th>Mitra Kerja Sama</th>
                    <th style="width: 10px">:</th>
                    <td>{{ $dataMapped['id_mitra'] ?? '-' }}</td>
                </tr>
                <tr>
                    <th>4.</th>
                    <th>Ruang Lingkup</th>
                    <th style="width: 10px">:</th>
                    <td>
                    {!! nl2br($dataMapped['ruang_lingkup']) !!}
                    </td>
                </tr>
                <tr>
                    <th>5.</th>
                    <th>Hasil Pelaksanaan (Output & Outcome)</th>
                    <th style="width: 10px">:</th>
                    <td>{!! nl2br($dataMapped['hasil_pelaksanaan']) !!}</td>
                </tr>
                <tr>
                    <th>6.</th>
                    <th>Tautan/Link Dokumentasi Kegiatan</th>
                    <th style="width: 10px">:</th>
                    <td>
                        <a href="{{ $dataMapped['link_dokumentasi'] }}">{{ $dataMapped['link_dokumentasi'] }}</a>
                    </td>
                </tr>
            </tbody>
        </table>
    
        <div class="mt-3">
            <div class="row mb-3">
                <div class="col-12 d-flex align-items-end pb-3">
                    <span>
                        PENANGGUNG JAWAB KEGIATAN
                        <br>
                        {{ Carbon\Carbon::now()->translatedFormat('l, d F, Y') }}
                    </span>
                </div>
                <div class="col-4 d-flex align-items-end">
                </div>
                <div class="col-4 d-flex align-items-end">
                    <span>Mitra</span>
                </div>
                <div class="col-4 d-flex align-items-end">
                    <span>Mengetahui,</span>
                </div>
            </div>
            <div class="row">
                <div class="col-4 d-flex flex-column align-items-start justify-content-between text-start" style="height: 200px">
                    <div class="">
                        <span>{{ $penanggungJawabUnit->jabatan }}</span> 
                        <br>
                        <span>{{ $universitas }}</span>
                    </div>
                    <div class="d-flex flex-column">
                        <span>{{ $penanggungJawabUnit->nama_penanggung_jawab }}</span>
                        <span>NIP: {{ $penanggungJawabUnit->nip }}</span>
                    </div>
                </div>

                <div class="col-4 d-flex flex-column align-items-start text-start">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div class="">
                            <span>{{ $penanggungJawabMitra->jabatan }}</span> 
                            <br>
                            <span>{{ $dataMapped['id_mitra'] }}</span>
                        </div>
                        <div class="d-flex flex-column">
                            <span>{{ $penanggungJawabMitra->nama_penanggung_jawab }}</span>
                            <span>NIP: {{ $penanggungJawabMitra->nip }} </span>
                        </div>
                    </div>
                </div>

                <div class="col-4 d-flex flex-column align-items-start justify-content-between text-start">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div class="">
                            <span>(........................),</span>
                        </div>
                        <div class="">
                            <span>....................................</span>
                            <br>
                            <span>NIP:.............................</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-core::quantum-3.layoutsh.html>