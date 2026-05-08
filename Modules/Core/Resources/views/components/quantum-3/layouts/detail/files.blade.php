@props([
    'data' => [],
])
@php
    use Modules\Core\Helpers\Format;
    $documentIds = [];
    foreach ($data as $data) {
        if (isset($data['original'])) {
            $documentIds[] = $data['original'];
        }
    }
    $data = Modules\DMS\Models\Dokumen::whereIn('id', $documentIds)
        ->get()
        ->map(function ($doc) {
            $doc->size = $doc->last_version_size;
            $doc->tempUrl = $doc->lastVersionTemporaryUrl();
            return $doc;
        })
        ->toArray();
@endphp

<div class="pt-3">
    <h5>Dokumen</h5>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="align-middle">
                <tr class="table-light">
                    <th>
                        Nama
                    </th>
                    <th>Ukuran</th>
                    <th>
                        Terakhir Diubah
                    </th>
                    <th class="cell-action cell-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if (empty($data))
                    <tr>
                        <td colspan="100" style="text-align: center">Tidak ada lampiran</td>
                    </tr>
                @endif
                @foreach ($data as $item)
                    @php
                        $ext = $item['extension_versi_terbaru'];
                        if ($item['extension_versi_terbaru'] == 'docx') {
                            $ext = 'doc';
                        }
    
                        $assetUrl = asset("images/$ext-solid.svg");
                    @endphp
                    <tr>
                        <td>
                            <div class="util_d-flex util_flex-center-vertical">
                                <img height="25px;" src="{{ $assetUrl }}"
                                    alt="">&nbsp;&nbsp;{{ $item['nama_dokumen'] }}.{{ $item['extension_versi_terbaru'] }}
                            </div>
                        </td>
                        <td>{{ Format::formatBytes($item['size']) }}</td>
                        <td>{{ Carbon\Carbon::parse($item['waktu_diubah'])->diffForHumans() }}</td>
                        <td class="cell-action">
                            <div class="dropdown-group" style="display: flex; align-items: center; gap: 4px">
                                <a href="{{ $item['tempUrl'] }}" rel="noopener" target="_blank"
                                    class="btn btn_outline btn_xs btn_icon" data-btn-label="Lihat File">
                                    <span class="icon icon-eye-solid"></span>
                                </a>
                                <div class="dropdown-group__target">
                                    <div class="dropdown-group__toggle">
                                        <a href="#" class="btn btn_outline btn_xs btn_icon">
                                            <span class="icon icon-ellipsis-horizontal"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
