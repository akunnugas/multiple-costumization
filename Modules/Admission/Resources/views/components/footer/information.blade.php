@php
    use \Modules\Gate\Models\Module;
    // FIXME: perbaiki get datanya dinamis sesuai settingan
@endphp
<div class="footer-landing-page">
    <div class="grid container">
        <div class="col-12 col-sm-4 col-md-4 col-lg-4">
            <div class="info-campus">
                <div class="info-campus-logo">
                    <img src="{{ Page::quantumAsset('images/logos/sevima.png') }}" alt="Logo Kampus">
                </div>
                <div class="info-campus-text">
                    <p>Seleksi Penerimaan Mahasiswa Baru</p>
                    <h1>Universitas Sevima</h1>
                </div>
            </div>
            <p class="desk-campus">
                lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, diam id tincidunt dapibus, diam
            </p>
            <div class="social-media">
                <a href="#" target="_blank">
                    <img src="{{ asset('images/admissions') }}/social-fb.svg" alt="">
                </a>
                <a href="#" target="_blank">
                    <img src="{{ asset('images/admissions') }}/social-tw.svg" alt="">
                </a>
                <a href="#" target="_blank">
                    <img src="{{ asset('images/admissions') }}/social-ig.svg" alt="">
                </a>
            </div>
        </div>
        <div class="cols-sm-1 col-md-1"></div>
        <div class="col-12 col-sm-4 col-md-4 col-lg-4">
            <div class="info-contact">
                <h3>Kontak Kami</h3>
                <ul>
                    <li>
                        <span class="icon icon-map-pin"></span>
                        Jl. Medokan Asri Tengah MA 2 Blok Q 16, Kel. Medokan Ayu, Kec. Rungkut, Kota Surabaya
                    </li>
                    <li>
                        <span class="icon icon-phone"></span>
                        031-879-2977
                    </li>
                    <li>
                        <span class="icon icon-inbox-stack"></span>
                        Ini adalah Fax
                    </li>
                    <li>
                        <span class="icon icon-envelope"></span>
                        info.siakadcloud@gmail.com
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-12 col-sm-3 col-md-3 col-lg-3">
            <div class="info-menu">
                <h3>Menu</h3>
                <ul>
                    <li id="link-beranda">
                        <a href="{{ url(Module::CODE_PMB_ADMISSION . '/') }}">
                            Beranda
                        </a>
                    </li>
                    <li id="link-program-studi">
                        <a href="{{ url(Module::CODE_PMB_ADMISSION . '/') }}">
                            Program Studi
                        </a></li>
                    <li id="link-pengumuman">
                        <a href="{{ route('admission.announcements') }}">
                            Informasi dan Pengumuman
                        </a>
                    </li>
                    <li id="link-jalur-seleksi">
                        <a href="{{ route('admission.registration-path') }}">
                            Jalur Pendaftaran
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
