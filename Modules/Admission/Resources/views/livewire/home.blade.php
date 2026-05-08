<div>
    @push('head')
        <style>
            #home-banner {
                background: radial-gradient(47.81% 88.03% at 82.08% 33.88%, rgba(33, 33, 33, 0) 30.73%, rgba(33, 33, 33, 0.9) 100%), url({{ $backgroundBannerSection }});
                background-repeat: no-repeat;
                background-size: cover;
            }
        </style>
    @endpush

    {{-- Mobile Login Start --}}
    <x-admission::header.navbar-mobile />
    {{-- Mobile Login End --}}

    {{-- Banner Start --}}
    <section id="home-banner">
        <div class="container">
            <div class="grid">
                <div class="col-12">
                    <div class="copy-mobile">
                        <h1>Cari Jalur Pendaftaran</h1>
                        <p>Temukan jalur pendaftaran sesuai dengan pilihan program studi yang diminati.</p>
                    </div>
                    <div class="copy-desktop">
                        <h1>{{ $homeTitle }}</h1>
                        <p>Cari tau informasi program studi, cost kuliah, dan informasi pendaftaran di
                            <br class="util_d-sm-none util_d-lg-block" />

                            {{ $universityName }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- Banner End --}}

    {{-- Pencarian Home Start --}}
    <section id="home-registration-path">
        <div class="container">
            <div class="grid">
                <div class="col-12">
                    <div class="card">
                        <div class="card__body">
                            <div class="copy-desktop">
                                <h1>Cari Jalur Pendaftaran</h1>
                                <p>Temukan jalur pendaftaran sesuai dengan pilihan program studi yang diminati.</p>
                            </div>
                            @php
                                // FIXME: optionnya harusnya dinamis dan handle di livewire nya belum
                            @endphp
                            <x-admission::search-registration-path :$degreeOpt :$studyProgramOpt :$lectureSystemOpt />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- Pencarian Home End --}}

    {{-- Cost Home Start --}}
        <section id="home-cost" class="util_d-block util_d-sm-none util_d-md-none util_d-lg-none">
            <div class="container">
                <div class="grid">
                    <div class="col-12">
                        <div class="card">
                            <div class="card__body">
                                <div class="cost">
                                    <div>
                                        <h3>Brosur dan Informasi Biaya</h3>
                                        <p>Brosur dan rincian biaya selama kuliah</p>
                                    </div>
                                    <x-core::button :variant="'outline'" :disabled="empty($pmbBrochure)">
                                        {{ __('admission::home.see_detail') }}
                                    </x-core::button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    {{-- Cost Home End --}}

    {{-- Informasi Program Studi Start  --}}
    <section id="home-program">
        <div class="container">
            <div class="grid">
                <div class="col-12 col-sm-12 col-md-12 col-lg-8">
                    <div class="card card-program">
                        <div class="card__body">
                            <div class="title">
                                <h1>
                                    {{ __('admission::home.information') . ' ' . __('admission::home.general_program') }}
                                </h1>
                            </div>
                            @php
                                // FIXME: navigationnya bisa pakai livewire, dan get list prodinya get dari backend livewire
                            @endphp
{{--                            <ul class="nav nav-pills category-program" id="pills-tab" role="tablist">--}}
{{--                                <li class="nav-item" role="presentation">--}}
{{--                                    <button class="tag-category-program active" id="pills-D4-tab" data-bs-toggle="pill" data-bs-target="#pills-D4" type="button" role="tab">D4 - Diploma 4</button>--}}
{{--                                </li>--}}
{{--                                <li class="nav-item" role="presentation">--}}
{{--                                    <button class="tag-category-program " id="pills-S1-tab" data-bs-toggle="pill" data-bs-target="#pills-S1" type="button" role="tab">S1 - Strata 1</button>--}}
{{--                                </li>--}}
{{--                            </ul>--}}
                            <nav class="nav-tab nav-tab_phill-sm">
                                <ul class="nav-tab__wrapper">
                                    @foreach($programDegrees as $degree)
                                        <li @class([
                                            'nav-tab__item',
                                            'active' => $loop->first,
                                        ]) data-toggle="tab" data-target="#pills-{{ $degree->id }}">
                                            {{ $degree->code . ' - ' . $degree->name }}
                                        </li>
                                    @endforeach
                                    <li class="nav-tab__item active" data-toggle="tab" data-target="#pills-D4">
                                        D4 - Diploma 4
                                    </li>
                                    <li class="nav-tab__item" data-toggle="tab" data-target="#menu2">
                                        S1 - Strata 1
                                    </li>
                                    <li class="nav-tab__item" data-toggle="tab" data-target="#menu3">
                                        S2 - Strata 2
                                    </li>
                                    <li class="nav-tab__item" data-toggle="tab" data-target="#menu4">
                                        S3 - Strata 3
                                    </li>
                                </ul>
                            </nav>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="list-program tab-pane fade show active" id="pills-D4">
                                    <div class="card">
                                        <div class="card__body card-list-program">
                                            <div class="title-program">
                                                <h1 class="name-program">D4 - Akupuntur &amp; Pengobatan Herbal</h1>
                                                <div class="description">Tersedia 12 Jalur Pendaftaran</div>
                                            </div>
                                            <x-core::button target="_blank" href="{{ route('admission.programs.detail', 1) }}" variant="outline">
                                                {{ __('admission::home.see_detail') }}
                                            </x-core::button>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card__body card-list-program">
                                            <div class="title-program">
                                                <h1 class="name-program">D4 - Perhotelan dan Pariwisata</h1>
                                                <div class="description">Tersedia 2 Jalur Pendaftaran</div>
                                            </div>
                                            <x-core::button target="_blank" href="{{ route('admission.programs.detail', 1) }}" variant="outline">
                                                {{ __('admission::home.see_detail') }}
                                            </x-core::button>
                                        </div>
                                    </div>
                                    <div class="cta-more">
                                        <a href="#" class="link">
                                            lihat Semua Prodi D4 - Diploma 4
                                            <span class="icon icon-chevron-right"></span>
                                        </a>
                                    </div>
                                </div>
{{--                                <?php $i = 1; ?>--}}
{{--                                <?php foreach ($a_prodijenjang as $a_jenjang) : ?>--}}
{{--                                <div class="list-prodi tab-pane fade <?= $i == 1 ? 'show active' : '' ?>" id="pills-<?= $a_jenjang['idjenjang'] ?>">--}}
{{--                                        <?php foreach (array_slice($a_jenjang['prodi'], 0, 5) as $a_prodi) : ?>--}}
{{--                                    <div class="card-prodi">--}}
{{--                                        <div class="title-program">--}}
{{--                                            <h1 class="name-program"><?= $a_prodi['idjenjang'] ?> - <?= $a_prodi['namaunit'] ?></h1>--}}
{{--                                                <?php if ($a_prodi['pendaftarandibuka'] > 0) : ?>--}}
{{--                                            <div class="description">Tersedia <?= $a_prodi['pendaftarandibuka'] ?> Jalur Pendaftaran</div>--}}
{{--                                            <?php else : ?>--}}
{{--                                            <div class="description">Belum ada jalur yang buka</div>--}}
{{--                                            <?php endif; ?>--}}
{{--                                        </div>--}}
{{--                                        <a target="_blank" href="program-studi-detail/detail/<?= $a_prodi['idunit'] ?>">--}}
{{--                                            <button class="button-pmb_primary-outline">Lihat Detail</button>--}}
{{--                                        </a>--}}
{{--                                    </div>--}}
{{--                                    <?php endforeach; ?>--}}
{{--                                        <?php if (count($a_jenjang['prodi']) > 5) : ?>--}}
{{--                                    <div class="cta-more">--}}
{{--                                        <a href="<?= Route::getNavAddress('program-studi') . '?jenjang=' . $a_jenjang['idjenjang'] ?>">lihat Semua Prodi <?= $a_jenjang['idjenjang'] ?> - <?= $a_jenjang['namajenjang'] ?> <span class="material-icons"> chevron_right </span></a>--}}
{{--                                    </div>--}}
{{--                                    <?php endif; ?>--}}
{{--                                </div>--}}
{{--                                    <?php $i++; ?>--}}
{{--                                <?php endforeach; ?>--}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-12 col-lg-4 util_d-none util_d-sm-block util_d-md-block util_d-lg-block">
                    <div class="card card-brochure">
                        <div class="card__body">
                            <div class="cost">
                                <div>
                                    <h3>Brosur dan Informasi biaya</h3>
                                    <p>Brosur dan rincian biaya selama kuliah di {{ $universityName }}</p>
                                </div>
                                <x-core::button :variant="'outline'" :disabled="empty($pmbBrochure)">
                                    {{ __('admission::home.see_detail') }}
                                </x-core::button>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card__body">
                            <div class="procedures">
                                <h3>Tata Cara Pendaftaran</h3>
                                <ol>
                                    <li>Pilih jalur pendaftaran</li>
                                    <li>Lengkapi formulir pendaftaran</li>
                                    <li>Selesaikan pembayaran formulir</li>
                                    <li>Lengkapi berkas dan ikuti seleksi pendaftaran</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="line-bold"></div>
    </section>
    {{-- Informasi Program Studi End  --}}

    {{-- Informasi dan Pengumuman Start --}}
    <section id="home-announcement">
        <div class="container">
            <div class="grid">
                <div class="col-12 util_p-0 util_m-0">
                    <div class="list">
                        <div class="title">
                            <h1>Informasi & Pengumuman</h1>
                            <a href="{{ route('admission.announcements') }}" class="link">Lihat Semua Informasi <span class="icon icon-chevron-right"></span></a>
                        </div>

                        <x-admission::news :data="$news" />
{{--                        <div class="grid">--}}
{{--                            <?php foreach ($a_pengumuman as $pengumuman) :--}}
{{--                                $i = 1;--}}
{{--                                $thumb = Route::getImageURL(mPengumuman::IMGDIR, $pengumuman['idpengumuman'], false, null, true);--}}
{{--                                ?>--}}
{{--                            <div class="col-md-4 col-lg-3 col-sm-6 col-12">--}}
{{--                                <a id="link-pengumuman-<?= $i++ ?>" href="<?= Route::getNavAddress('detail-pengumuman/' . mPengumuman::getIDLink($pengumuman)) ?>" class="card-news">--}}
{{--                                        <?php if ($thumb) : ?>--}}
{{--                                    <div class="thumbnail">--}}
{{--                                        <img loading="lazy" src="<?= $thumb ?>" alt="banner">--}}
{{--                                    </div>--}}
{{--                                    <?php else : ?>--}}
{{--                                    <div class="thumbnail">--}}
{{--                                        <div class="empty">--}}
{{--                                            <img loading="lazy" src="<?= Auth::getSettingSIM('university_logo') ?>" alt="banner" style="object-fit: contain;">--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <?php endif; ?>--}}
{{--                                    <div class="right-content">--}}
{{--                                        <p class="date"><?= CStr::formatDateInd($pengumuman['tglpengumuman'], true, false, '-', false) ?></p>--}}
{{--                                        <h1 class="title-news"><?= $pengumuman['judulpengumuman'] ?></h1>--}}
{{--                                            <?php if ($pengumuman['jenis'] == mPengumuman::INFORMASI) : ?>--}}
{{--                                        <div class="badge badge--info">Informasi</div>--}}
{{--                                        <?php elseif ($pengumuman['jenis'] == mPengumuman::PENGUMUMAN) : ?>--}}
{{--                                        <div class="badge badge--warning">Pengumuman</div>--}}
{{--                                        <?php endif; ?>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                            <?php endforeach; ?>--}}
{{--                        </div>--}}
                        <div class="cta-more">
                            <a href="{{ route('admission.announcements') }}" class="link">Lihat Semua Informasi <span class="icon icon-chevron-right"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="line-bold"></div>
    </section>
    {{-- Informasi dan Pengumuman End --}}
</div>
