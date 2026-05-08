<div>
    {{-- Breadcrumb --}}
    <x-admission::breadcrumb :title="$title" :parentNav="$parentNav" />
    <section class="util_margin-top-fix" id="home-registration-path">
        <div class="container">
            <div class="grid util_row-gap-0">
                <div class="col-12">
                    <div class="card util_border-radius-bottom-0">
                        <div class="card__body">
                            <div class="copy-desktop">
                                <h1>Cari Jalur Pendaftaran</h1>
                                <p>Temukan jalur pendaftaran sesuai dengan pilihan program studi yang diminati.</p>
                            </div>
                            @php
                                // FIXME: optionnya harusnya dinamis dan handle di livewire nya belum
                            @endphp
                            <x-admission::search-registration-path :degreeOpt="[]" :studyProgramOpt="[]"
                                :studySystemOpt="[]" />
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card util_border-radius-top-0 util_border-top-none">
                        <div class="card__body">
                            <div class="copy-desktop">
                                <h1>Tata Cara Pendaftaran</h1>
                                <ol>
                                    <li>Pilih jalur pendaftaran</li>
                                    <li>Lengkapi formulir pendaftaran</li>
                                    <li>Selesaikan pembayaran formulir</li>
                                    <li>Lengkapi berkas dan ikuti seleksi</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="registration-path-list">
        <div class="container util_py-0">
            <div class="grid">
                <div class="col-12">
                    <x-admission::card-registration-path :data="$registrationPaths" />
                </div>
            </div>
        </div>
    </section>
</div>
