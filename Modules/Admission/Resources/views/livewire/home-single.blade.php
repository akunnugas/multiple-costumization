<div class="util_margin-top-fix">
    <div id="registration-section">
        <img src="{{ asset('images/admissions') }}/pt.jpg">
        <div class="container">
            <div class="title">
                <h2 class="util_mb-10">Penerimaan Mahasiswa Baru Telah Dibuka !</h2>
                <x-core::badge variant="warning" class="util_mb-10">Tahun Akademik <span
                        class="util_text-bold">2024/2025</span></x-core::badge>

            </div>
            <x-admission::registration-form :$data :$alert />
        </div>
    </div>
    @empty(!$content['programs'])
        <div id="program-section" class="container">
            <div class="title">
                <h2>Program Studi</h2>
                {{-- @php
                $degreeFilter = [0 => 'Semua', 1 => 'S1', 2 => 'D3'];
            @endphp
            <x-core::select :options="$degreeFilter" /> --}}
            </div>
            <div class="util_scrollable">
                <div class="card-container">
                    @foreach ($content['programs'] as $program)
                        <div class="card program">
                            <img
                                src="{{ !empty($program->slug) ? asset('images/admissions/') . $program->slug : asset('images/admissions/no-image.png') }}">
                            <div class="card__body">
                                <h2>{{ $program->name ?? '' }}</h2>
                                <x-core::badge variant="default" size="sm">Kelas Pagi</x-core::badge>
                                <div class="information">
                                    <p>
                                        {{ $program->description ?? '-' }}
                                    </p>
                                    <div class="additional-information">
                                        <div class="item">
                                            <x-core::icon type="check-badge" />
                                            <span>
                                                {{ $program->accreditation_grade ? 'Terakreditasi ' . $accreditation_list[$program->accreditation_grade] : 'Belum Terakreditasi' }}</span>
                                        </div>
                                        <div class="item">
                                            <x-core::icon type="banknotes" />
                                            <span>Mulai Dari Rp 2.000.000,-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endempty
    @empty(!$content['facilities'])
        <div id="facility-section" class="container">
            <div class="title">
                <h2>Fasilitas</h2>
            </div>
            <div class="util_scrollable">
                <div class="card-container">
                    @foreach ($content['facilities'] as $facility)
                        <div class="card facility">
                            <img
                                src="{{ !empty($facility->slug) ? asset('images/admissions/') . $facility->slug : asset('images/admissions/no-image.png') }}">
                            <h3>{{ $facility->title ?? '' }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- <div class="facility-nav">
            <a class="active" href="#"></a>
            <a href="#"></a>
            <a href="#"></a>
        </div> --}}
        </div>
    @endempty
    @empty(!$content['alumni'])
        <div id="alumni-section" class="container">
            <div class="title">
                <h2>Alumni</h2>
            </div>
            <div class="util_scrollable">
                <div class="card-container">
                    @foreach ($content['alumni'] as $alumni)
                        <div class="card alumni">
                            <div class="profile-photo">
                                <img
                                    src="{{ !empty($alumni->slug) ? asset('images/admissions/') . $alumni->slug : asset('images/admissions/no-image.png') }}">
                                <div class="description">
                                    {{ $alumni->additional_info }}
                                </div>
                            </div>
                            <div class="card__body quotes">
                                <p><q>{{ $alumni->description }}</q></p>
                            </div>
                        </div>
                        @endforeach
                        {{-- <div class="card alumni">
                            <div class="profile-photo">
                                <img src="https://source.unsplash.com/woman-in-black-blazer-sitting-on-chair-Mis5fyJi7Q0">
                                <div class="description">
                                    <h3>Ayu Firda Amalia</h3>
                                    <p>S1 Sistem Informasi - System Analyst di Sevima</p>
                                </div>
                            </div>
                            <div class="card__body quotes">
                                <p><q>Telkom itu selain kampusnya bagus, akreditasinya juga bagus.
                                        Banyak banget informasi yang aku dapatkan dan ternyata diperlukan pada saat di dunia
                                        kerja.
                                        Sebagai Fashion Photographer yang bisa diambil adalah bagaimana cara mengatur bisnis
                                        fotografi dan diaplikasikan di dunia nyata.</q></p>
                            </div>
                        </div> --}}
                </div>
            </div>
        </div>
    @endempty
    <div id="announcement-section" class="container">
        <div class="title">
            <h2>Pengumuman</h2>
        </div>
        <div class="announcement-list">
            <div class="card">
                <a href="#">
                    <div class="card__body announcement">
                        <x-core::icon type="megaphone" style="font-size: 20px" />
                        <div>
                            <h3>Pengumuman Kelulusan Gelombang 1</h3>
                            <p>12 Desember 2023</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="card">
                <a href="#">
                    <div class="card__body announcement">
                        <x-core::icon type="megaphone" style="font-size: 20px" />
                        <div>
                            <h3>Pengumuman Kelulusan Gelombang 2</h3>
                            <p>12 Desember 2023</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="card">
                <a href="#">
                    <div class="card__body announcement">
                        <x-core::icon type="megaphone" style="font-size: 20px" />
                        <div>
                            <h3>Pengumuman Kelulusan Gelombang 3</h3>
                            <p>12 Desember 2023</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div id="footer-section" class="container">
    </div>
</div>
