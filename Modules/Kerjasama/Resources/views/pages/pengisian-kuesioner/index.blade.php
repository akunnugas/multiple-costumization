@php
use Modules\Core\Helpers\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

$rawData->pertanyaan = collect($rawData->pertanyaan)->sortBy('nomor')->values()->all();
@endphp
<x-kerjasama::layouts.main-outer-external :withContainer="false">

    <style>
        .option-card {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border: 2px solid #dee2e6;
        }

        .option-card:hover {
            border-color: #adb5bd;
            background-color: #f8f9fa;
        }

        .option-card.active {
            border-color: #0d6efd;
            background-color: #e7f1ff;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        .star-rating {
            direction: rtl;
            display: inline-flex;
            font-size: 2rem;
            cursor: pointer;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            color: #dee2e6;
            transition: color 0.2s;
            cursor: pointer;
            padding: 0 0.2rem;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input:checked~label {
            color: #ffc107;
        }

        .option-card input[type="radio"] {
            display: none;
        }
    </style>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">

                {{-- Form Start --}}
                <form id="kuesionerForm" action="{{ route('kerjasama.kuesioner.store', $rawData->uuid) }}" method="POST" novalidate>
                    @csrf

                    @if(session('success'))
                    <div class="alert alert-success shadow-sm mb-4 border-0 rounded-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle fs-4"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Berhasil!</h5>
                                <p class="mb-0">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Header Card --}}
                    <div class="card shadow-sm mb-4 border-top border-5 border-primary rounded-3">
                        <div class="card-body p-4">
                            <h2 class="display-6 fw-bold mb-2">{{ $rawData->judul_evaluasi }}</h2>
                            <p class="lead text-muted fs-6">{{ $rawData->deskripsi ?? 'Silakan isi kuesioner ini dengan sebenar-benarnya.' }}</p>

                            @if($rawData->selesai)
                            <div class="text-danger small mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i> Batas pengisian: {{ \Carbon\Carbon::parse($rawData->selesai)->translatedFormat('d F Y') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Data Diri Card --}}
                    <div class="card shadow-sm mb-4 rounded-3">
                        <div class="card-header bg-white p-4 pb-0 border-0">
                            <h5 class="fw-bold mb-0">Data Diri</h5>
                            <p class="text-muted small">Mohon lengkapi identitas Anda di bawah ini.</p>
                        </div>
                        <div class="card-body p-4 pt-2">
                            <div class="mb-3">
                                <x-core::quantum-3.controls.form
                                    label="Nama Lengkap"
                                    id="nama"
                                    name="nama"
                                    required
                                    placeholder="Contoh: Budi Santoso"
                                    :value="old('nama')"
                                    type="text" />
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <x-core::quantum-3.controls.form
                                        label="No. Telepon / WA"
                                        id="phone"
                                        required
                                        name="phone"
                                        placeholder="Contoh: 081234567890"
                                        :value="old('phone')" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <x-core::quantum-3.controls.form
                                        label="Email"
                                        type="email"
                                        id="email"
                                        name="email"
                                        required
                                        placeholder="Contoh: email@example.com"
                                        :value="old('email')" />
                                </div>
                            </div>

                            <!-- <div class="mb-3">
                                <label for="unit_kerja_id" class="form-label fw-medium">Unit Kerja</label>
                                <select class="form-select" id="unit_kerja_id" name="unit_kerja_id">
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    @foreach($unitKerjaOptions as $id => $label)
                                        <option value="{{ $id }}" {{ old('unit_kerja_id') == $id ? 'selected' : '' }}>{!! $label !!}</option>
                                    @endforeach
                                </select>
                            </div> -->
                        </div>
                    </div>

                    {{-- Questions Loop --}}
                    @foreach ($rawData->pertanyaan as $pertanyaan)
                    <div class="card shadow-sm mb-3 rounded-3">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label d-block fw-medium fs-5">
                                    {{ $pertanyaan->nomor }}. {{ $pertanyaan->pertanyaan }}
                                    @if ($pertanyaan->apakah_wajib)
                                    <span class="text-danger" title="Wajib diisi">*</span>
                                    @endif
                                </label>
                                @if ($pertanyaan->deskripsi)
                                <div class="form-text text-muted mb-2">{{ $pertanyaan->deskripsi }}</div>
                                @endif
                            </div>

                            <div class="mb-2">
                                {{-- Input Logic --}}
                                @if ($pertanyaan->tipe == 'text' || $pertanyaan->tipe == 'essay')
                                <div class="col-md-12 mb-12">
                                    <x-core::quantum-3.controls.form
                                        label="Jawaban Anda"
                                        id="jawaban_{{ $pertanyaan->id }}"
                                        name="jawaban[{{ $pertanyaan->id }}]"
                                        placeholder="Jawaban Anda"
                                        :value="old('jawaban.' . $pertanyaan->id)"
                                        is_array="true"
                                        type="text" />
                                </div>
                                @elseif ($pertanyaan->tipe == 'option')
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($pertanyaan->opsiJawaban as $opsi)
                                    <label class="option-card p-3 rounded-3 d-flex align-items-center {{ old('jawaban.' . $pertanyaan->id) == $opsi->id ? 'active' : '' }}" onclick="selectOption(this)">
                                        <input type="radio" name="jawaban[{{ $pertanyaan->id }}]" id="opsi_{{ $opsi->id }}" value="{{ $opsi->id }}" {{ $pertanyaan->apakah_wajib ? 'required' : '' }} {{ old('jawaban.' . $pertanyaan->id) == $opsi->id ? 'checked' : '' }}>
                                        <span class="fw-medium text-dark">{{ $opsi->jawaban }}</span>
                                        
                                    </label>
                                    @endforeach
                                     @error('jawaban.' . $pertanyaan->id)
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                @elseif ($pertanyaan->tipe == 'rating')
                                <div class="d-flex align-items-center justify-content-center my-3">
                                    <div class="star-rating">
                                        @for ($i = ($pertanyaan->rating ?? 5); $i >= 1; $i--)
                                        <input type="radio" name="jawaban[{{ $pertanyaan->id }}]" id="rating_{{ $pertanyaan->id }}_{{ $i }}" value="{{ $i }}" {{ $pertanyaan->apakah_wajib ? 'required' : '' }} {{ old('jawaban.' . $pertanyaan->id) == $i ? 'checked' : '' }}>
                                        <label for="rating_{{ $pertanyaan->id }}_{{ $i }}" title="{{ $i }} Bintang">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor" width="1em" height="1em" style="vertical-align: -0.125em;">
                                                <path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z" />
                                            </svg>
                                        </label>
                                        @endfor
                                    </div>
                                   
                                </div>
                                <div class="text-center text-muted small mt-1">
                                    (1 = Sangat Buruk, {{ $pertanyaan->rating ?? 5 }} = Sangat Baik)
                                </div>
                                 @error('jawaban.' . $pertanyaan->id)
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                @endif

                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Submit Button --}}
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
                        @if(isset($isScheduled) && $isScheduled)
                             <div class="alert alert-warning mb-0">
                                <i class="fas fa-clock me-1"></i> Form ini belum dibuka untuk pengisian.
                            </div>
                        @else
                            <div class="text-muted small">
                                Jangan lupa periksa kembali jawaban Anda.
                            </div>
                        @endif
                        
                        <x-core::quantum-3.button type="submit" id="btnSubmit" variant="primary" size="lg" trailingIcon="send-03" :disabled="$isScheduled ?? false">
                            Kirim Jawaban
                        </x-core::quantum-3.button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function selectOption(element) {
            const container = element.parentElement;
            const siblings = container.querySelectorAll('.option-card');
            siblings.forEach(el => el.classList.remove('active'));

            element.classList.add('active');

            const radio = element.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        document.getElementById('kuesionerForm').addEventListener('submit', function() {
            var btn = document.getElementById('btnSubmit');

            btn.disabled = true;
            btn.innerHTML = '<div class="d-flex align-items-center justify-content-center"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Mengirim...</div>';
        });
    </script>
    @endpush

</x-core::quantum-3.layouts.main-outer-external>