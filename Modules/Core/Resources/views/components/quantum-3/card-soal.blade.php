@props([
    'number' => '',
    'question' => '',
    'options' => [], // array: ['Pilihan Jawaban 1', 'Pilihan Jawaban 2', ...]
    'required' => false,
    'editUrl' => '#',
    'type' => '',
    'evaluasi' => null,
    'pertanyaanId' => null,
    'scale' => null
])


   
    <div class="card shadow-sm mb-1">
    <div class="card-body">




        {{-- Nomor & Pertanyaan --}}
        <div class="row mb-3">

            <div class="col">
                <label class="form-label fw-bold">Nomor </label> {{ $number }}
                <hr style="margin: 0; margin-bottom: 10px;">
                <label class="form-label fw-bold">Pertanyaan</label>
                <div
                    class="option-box border rounded-3 px-3 py-2 bg-white shadow-sm d-block align-items-center"
                    style="width:100%">{{ $question }}</div>
            </div>
        </div>

        {{-- Pilihan Jawaban --}}
        @if ($type === 'option')
        <div class="mb-4">
            <label class="form-label fw-bold">Pilihan Jawaban</label>
            <div class="row">
                @foreach ($options as $opt)
                <div class="col-md-6 mb-2">
                    <div
                        class="option-box border rounded-3 px-3 py-2 bg-white shadow-sm d-flex align-items-center">
                        <span class="fw-semibold text-dark">{{ $opt }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @elseif ($type === 'rating')
        <div class="mb-4">
            <label class="form-label fw-bold mb-2">Skala Rating (1 - {{ $scale }})</label>
            <div class="d-flex gap-2 flex-wrap text-warning fs-3">
                @for ($i = 1; $i <= $scale; $i++)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor" width="1em" height="1em" style="vertical-align: -0.125em;">
                        <path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z" />
                    </svg>
                @endfor
            </div>
            <div class="text-muted small mt-1">
                (1 = Sangat Buruk, {{ $scale }} = Sangat Baik)
            </div>
        </div>
        @endif
        <div class="d-flex justify-content-between align-items-center mt-4">
            <ul class="list-inline mb-0">
                <li class="list-inline-item align-middle">
                    <span class="fw-bold me-2">Wajib?</span>
                    <span class="badge {{ $required ? 'bg-success' : 'bg-secondary' }}">
                        {{ $required ? 'Ya' : 'Tidak' }}
                    </span>
                </li>
                @if($type)
                <li class="list-inline-item align-middle">
                    <span class="fw-bold me-2">Tipe Pertanyaan</span>
                    <span class="badge bg-primary text-white ms-1">
                        {{ $type }}
                    </span>
                </li>
                @endif
            </ul>
            {{-- Tombol Edit --}}
            <a href="{{ route('kerjasama.evaluasi-kuesioner.edit', ['evaluasi_kuesioner' => $evaluasi]) . (isset($pertanyaanId) ? '#'.$pertanyaanId : '') }}" class="btn btn-outline-primary">
                Edit
            </a>
        </div>

    </div>
    </div>