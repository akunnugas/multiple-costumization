@props([
    'number' => '',
    'question' => '',
    'options' => [], // array: ['Pilihan Jawaban 1', 'Pilihan Jawaban 2', ...]
    'required' => false,
    'editUrl' => '#',
    'type' => '',
    'pertanyaanId' => null, 
    'peserta'   => null,
    'pertanyaan' => null
])
@foreach ($pertanyaan as $item)
    <div class="card shadow-sm mb-1">
    <div class="card-body">
    {{-- Nomor & Pertanyaan --}}
    <div class="row mb-3">
        <div class="col">
            <label class="form-label fw-bold">Nomor </label> {{ $item->nomor }}
            <hr style="margin: 0; margin-bottom: 10px;">
            <label class="form-label fw-bold">
            Pertanyaan {{ $item->id }}
            
            </label>
            <div
                class="option-box border rounded-3 px-3 py-2 bg-white shadow-sm d-block align-items-center"
                style="width:100%">{{ $item->pertanyaan }}</div>
        </div>
    </div>
        {{-- Pilihan Jawaban --}}
        @if ($item->tipe === 'option')
            @php
                $total = \Modules\Kerjasama\Models\JawabanPeserta::where('pertanyaan_id', $item->id)->count();
                $optionCounts = [];
                $opsiJawabans = \Modules\Kerjasama\Models\OpsiJawaban::where('pertanyaan_id', $item->id)->orderBy('urutan')->get();
                
                foreach ($opsiJawabans as $opsi) {


                    $count = \Modules\Kerjasama\Models\JawabanPeserta::where('pertanyaan_id', $item->id)
                        ->where('opsi_jawaban_id', $opsi->id)
                        ->count();
                    $optionCounts[] = [
                        'option' => $opsi->jawaban,
                        'count' => $count,
                        'percent' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                    ];
                }



            @endphp
            <div class="mb-4">
                <label class="form-label fw-bold">Rekap Jawaban</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Pilihan Jawaban</th>
                            <th>Responden</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionCounts as $row)
                            <tr>
                                <td>{{ $row['option'] }}</td>
                                <td>{{ $row['count'] }}</td>
                                <td>{{ $row['percent'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        <!-- Rating -->
        @elseif ($item->tipe === 'rating')
            @php
                $total = \Modules\Kerjasama\Models\JawabanPeserta::where('pertanyaan_id', $item->id)->count();
                $ratingCounts = [];
                for ($i = 1; $i <= $item->rating; $i++) {
                    $count = \Modules\Kerjasama\Models\JawabanPeserta::where('pertanyaan_id', $item->id)
                        ->where('jawaban', $i)
                        ->count();
                    $ratingCounts[] = [
                        'rating' => $i,
                        'count' => $count,
                        'percent' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                    ];
                }
            @endphp
            <div class="mb-4">
                <label class="form-label fw-bold">Rekap Jawaban</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Rating</th>
                            <th>Responden</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ratingCounts as $row)
                            <tr>
                                <td>{{ $row['rating'] }}</td>
                                <td>{{ $row['count'] }}</td>
                                <td>{{ $row['percent'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>    
                </table>
            </div>

        @else
            @php
                $answers = collect();
                if ($peserta) {
                    $pesertaList = is_iterable($peserta) ? $peserta : [$peserta];
                    foreach ($pesertaList as $p) {
                        $answers = $answers->merge(
                            $p->jawaban()->where('pertanyaan_id', $item->id)->whereNull('tipe_jawaban')->get()
                        );
                    }
                }

                // Filter answers where logic is empty or null
                $answers = $answers->filter(function ($ans) {
                    return $ans->jawaban !== null && trim($ans->jawaban) !== '';
                });
            @endphp
            <div class="mb-4">
                <label class="form-label fw-bold">Jawaban Responden</label>
                  @if ($answers->isEmpty())
                             <div class="alert alert-info">
                        Belum ada responden yang menjawab pertanyaan ini.
                    </div>
                        @else
                <table class="table table-bordered">
                <thead>
                        <tr>
                            <th>Responden</th>
                            <th>Jawaban</th>
                        </tr>
                    </thead>
                    <tbody>
                       

                        @foreach ($answers as $ans)
                            <tr>
                                <td>{{ $ans->peserta->nama }}</td>
                                <td>{{ $ans->jawaban }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        @endif
    </div>
    </div>
@endforeach