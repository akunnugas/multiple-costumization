@props([
    'title' => null,
])
@php
    use Modules\SPMI\Models\PenilaianMatriks;
    use Modules\SPMI\Models\PenilaianSkor;
    // default title
    $title ??= $resourceTitle;
    $no = 1;
@endphp
<x-core::layouts.reports.show :title="$title" :pageback="$pageback">
    <div class="header-content">
        <h1>LAPORAN AMI</h1>
        <h4>Daftar Pertanyaan (Checklist)</h4>
    </div>
    <div class="information-content">
        <table>
            <tbody>
                @foreach($informations as $key => $value)
                    @php
                        $colspan = $isHasAsign ? 2 : 1;
                    @endphp
                    @if (!is_array($value))
                    <tr>
                        <td class="title" colspan="{{ $colspan }}">{{ $key }}</td>
                        <td class="value">{{ $value }}</td>
                    </tr>
                    @else
                    <tr class="asign">
                        <td rowspan="2" class="title">{{ $key }}</td>
                        <td class="title">Nama</td>
                        <td class="value">{{ $value['value'] }}</td>
                    </tr>
                    <tr class="asign">
                        <td class="title">Tanda Tangan</td>
                        <td class="value">
                            <div class="box-asign">

                            </div>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <h4>Daftar Pertanyaan dan Hasil Obeservasi</h4>
    <div class="content">
        <table class="table table-data">
            <thead>
                <th>No. Checklist</th>
                <th>No. Butir</th>
                <th>Elemen dan Indikator</th>
                <th>Pertanyaan dan Hasil Observasi saat Audit Lapangan</th>
            </thead>
            <tbody>
                @foreach ($assessmentMatrices as $item)
                    @php
                        $item = (array) $item;
                        $isElement = $item['category'] == PenilaianMatriks::CATEGORY_ELEMENT;
                        $isIndicator = $item['category'] == PenilaianMatriks::CATEGORY_INDICATOR;
                        $isDimension = $item['category'] == PenilaianMatriks::CATEGORY_DIMENSION;

                        if ($isElement) {
                            continue;
                        } else if ($isIndicator && empty($item['feedback'])){
                            continue;
                        }
                    @endphp
                    <tr>
                        @unless ($isElement)
                            <td> {{ $no++ }}</td>
                            <td>
                                {{ $item['number'] }}
                            </td>
                        @endunless
                        <td>
                            <p>
                                {{ $item['question'] }}
                            </p>
                        </td>
                        @unless ($isElement)
                        <td>
                            {{ $item['feedback']}}
                        </td>
                        @endunless
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-core::layouts.reports.show>

