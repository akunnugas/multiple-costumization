<div>
    {{-- Breadcrumb --}}
    <x-admission::breadcrumb :title="$title" :parentNav="$parentNav" />
    <div class="util_margin-top-fix">
        <div class="container">
            <div class="grid">
                <div class="col-lg-2"></div>
                <div class="col-12 col-lg-8">
                    <section id="registration-success" class="card">
                        <div class="content" id="success-message">
                            <img src="../images/admissions/success-message.gif" width="200px" alt="">
                            <h1>Yay! Berhasil Melakukan Pendaftaran</h1>
                            <p class="util_text-center">Terima kasih telah menyelesaikan pembayaran. Kamu telah berhasil melakukan
                                pendaftaran,
                                silakan
                                masuk dengan akun dibawah ini.</p>
                        </div>
                        <div class="line-bold util_d-block"></div>
                        <div class="content">
                            <section id="account-info" class="card">
                                <div class="content">
                                    <div>
                                        <h5 class="util_text-secondary util_mb-10">ID Pendaftar</h5>
                                        <h4 class="util_text-default">201211000024</h4>
                                    </div>
                                    <div class="copy-btn">
                                        <div class="util_text-primary">Salin</div>
                                        <x-core::icon type="document-duplicate" />
                                    </div>
                                </div>
                                <hr>
                                <div class="content">
                                    <div>
                                        <h5 class="util_text-secondary util_mb-10">PIN</h5>
                                        <h4 class="util_text-default">01112023</h4>
                                    </div>
                                    <div class="copy-btn">
                                        <div class="util_text-primary">Salin</div>
                                        <x-core::icon type="document-duplicate" />
                                    </div>
                                </div>
                            </section>
                            <p>Informasi ini juga akan diberikan melalui email yang telah kamu daftarkan.</p>
                            <x-core::button variant="primary" type="submit" href="login">
                                Masuk Sekarang
                            </x-core::button>
                        </div>
                    </section>
                </div>
            </div>

        </div>
    </div>
</div>
