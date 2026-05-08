@props([
    'data'
])

@php
    // masa pendaftaran
    // change from 2023-12-19 09:48:00+07 - 2023-12-26 09:48:00+07 to 1 Mar 2020 - 5 Sep 2024
    $registrationPeriod = \Carbon\Carbon::parse($data['rp_opened_at'])->isoFormat('D MMM Y')
        . ' - ' . \Carbon\Carbon::parse($data['rp_closed_at'])->isoFormat('D MMM Y');

    // tarif formulir
    $rates = 'Gratis';
    // TODO: pengecekan tarif belum ada karena belum konek ke keuangan
    if (!empty($row['rates']) && $row['rp_is_paid']) { // jika berbayar
        $minRate = number_format($row['rates']['min'], 0, ',', '.');
        $maxRate = number_format($row['rates']['max'], 0, ',', '.');

        $rates = ($row['rates']['min'] == $row['rates']['max'])
            ? "Rp. $minRate"
            : "Rp. $minRate - Rp. $maxRate";
    }
@endphp

<div>
    {{-- Breadcrumb --}}
    <x-admission::breadcrumb :title="$title" :parentNav="$parentNav" />

    <div class="util_margin-top-fix">
        <div class="container">
            <div class="grid">
                <div class="col-12 col-md-8">
                    <section id="registration-path-detail" class="card">
                        <div class="content">
                            <h1>{{ $title }}</h1>
                        </div>
                        <div class="steps-desktop">
                            <div class="steps" style="width: 25%;"></div>
                        </div>
                        <div class="content">
                            <h4 class="util_mb-20">Detail Jalur Pendaftaran</h4>
                            <p>
                                {{ $data['rp_description'] }}
                            </p>
                            <hr class="util_mb-20 util_mt-20">
                            <div class="grid">
                                <div class="col-md-2 col-4 util_text-secondary">Periode</div>
                                <div class="col-md-4 col-8 util_text-right util_text-default">
                                    {{ $data['period_name'] }}
                                </div>

                                <div class="col-md-2 col-4 util_text-secondary">Sistem Kuliah</div>
                                <div class="col-md-4 col-8 util_text-right util_text-default">
                                    {{ $data['lecture_system_name'] }}
                                </div>

                                <div class="col-md-2 col-4 util_text-secondary">Masa Pendaftaran</div>
                                <div class="col-md-4 col-8 util_text-right util_text-default">
                                    {{ $registrationPeriod }}
                                </div>

                                <div class="col-md-2 col-4 util_text-secondary">Gelombang</div>
                                <div class="col-md-4 col-8 util_text-right util_text-default">
                                    {{ $data['batch_name'] }}
                                </div>

                                <div class="col-md-2 col-4 util_text-secondary">Jenjang</div>
                                <div class="col-md-4 col-8 util_text-right util_text-default">
                                    {{ $data['degrees'] }}
                                </div>

                                <div class="col-md-2 col-4 util_text-secondary">Biaya Daftar</div>
                                <div class="col-md-4 col-8 util_text-right util_text-warning">
                                    {{ $rates }}
                                </div>
                            </div>
                        </div>

                        <div class="line-bold util_d-block"></div>
                        <div class="content">
                            <h4 class="util_mb-20">Persyaratan Administrasi</h4>
                            @if(!empty($data['administration_requirements']))
                                <p class="util_mb-20">Silakan siapkan beberapa informasi dan dokumen berikut untuk
                                    mempercepat proses pendaftaran.</p>
                                <ul class="util_text-secondary util_pl-20 util_mb-10">
                                    @foreach($data['administration_requirements'] as $requirement)
                                        <li class="util_mb-5">{{ $requirement['assessment_requirement_name'] }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>Tidak ada persyaratan administrasi yang dibutuhkan.</p>
                            @endif
                        </div>

                        @if($data['rp_is_quota_displayed'] && !empty($data['quotas']))
                            <div class="line-bold util_d-block"></div>
                            <div class="content">
                                <h4 class="util_mb-20">Daya Tampung</h4>
                                @foreach($data['quotas'] as $degreeCode => $degreeData)
                                    <h5 class="util_mb-10">{{ $degreeCode . ' - ' . $degreeData['degree_name'] }}</h5>
                                    <ol class="util_text-secondary util_pl-20 util_mb-10">
                                        @foreach($degreeData['study_programs'] as $studyProgram)
                                            <li class="util_mb-5">{{ $studyProgram['information'] }}</li>
                                        @endforeach
                                    </ol>

                                    @if($degreeData['total'] > 0)
                                        <h5 class="util_mb-20">Total {{ $degreeData['total'] }} Mahasiswa</h5>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($data['assessments']))
                            <div class="line-bold util_d-block"></div>
                            <div class="content">
                                <h4 class="util_mb-20">Jadwal Seleksi</h4>
                                @foreach($data['assessments'] as $assessment)
                                    <h5 class="util_mb-10">{{ $assessment['assessment_type_name'] }}</h5>
                                    <div class="util_mb-10">
                                        @if(!empty($assessment['started_at']) && !empty($assessment['ended_at']))
                                            @php
                                                $startedAt = \Carbon\Carbon::parse($assessment['started_at'])->isoFormat('D MMMM Y');
                                                $endedAt = \Carbon\Carbon::parse($assessment['ended_at'])->isoFormat('D MMMM Y');
                                            @endphp
                                            <p class="util_text-secondary">{{ $startedAt . ' - ' . $endedAt }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>
                <div class="col-12 col-md-4">
                    <section id="program-options" class="card">
                        <div class="content">
                            <h4 class="util_mb-10">Pilihan Program Studi</h4>
                            <p class="util_mb-10">Silakan pilih program studi yang kamu minati untuk melanjutkan proses
                                pendaftaran</p>
                            <div class="util_mb-20"></div>
                            <x-core::controls.select label="Program Studi" purpose="form" :options="$studyProgramOpt"
                                                     id="study-program-opt" wire:model="selectedStudyProgram" />
                            <div class="util_mb-20"></div>
                            @if($isRegistrationPeriodOpen)
                                <x-core::button variant="primary" wire:click="doNextStep()" class="util_w-100" id="btn-next-step">
                                    Lanjutkan Mendaftar <x-core::icon type="arrow-long-right"/>
                                </x-core::button>
                            @else
                                <x-core::button variant="primary" class="util_w-100" disabled>
                                    Lanjutkan Mendaftar <x-core::icon type="arrow-long-right"/>
                                </x-core::button>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function setSubmitButton(isDisabled) {
                document.getElementById('btn-next-step').disabled = isDisabled;
            }

            document.addEventListener("DOMContentLoaded", () => {
                setSubmitButton(true);
            });

            @if($isRegistrationPeriodOpen)
                // on change study program
                document.getElementById('study-program-opt').addEventListener('change', (e) => {
                    if (e.target.value == '') {
                        setSubmitButton(true);
                    } else {
                        setSubmitButton(false);
                    }
                });
            @endif
        </script>
    @endpush
</div>
