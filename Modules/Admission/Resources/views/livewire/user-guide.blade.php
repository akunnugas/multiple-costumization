<div>
    {{-- Breadcrumb --}}
    <x-admission::breadcrumb :title="$title" :parentNav="$parentNav" />
    <div class="util_margin-top-fix ">
        <div class="container">
            <div class="grid">
                <div class="col-12">
                    <div class="card util_border-solid">
                        <div class="card__body">
                            <h2 class="card__title">User Guide</h2>
                            <p class="card__subtitle">Dokumen pendukung atau user guide pendaftaran</p>
                        </div>
                        <hr>
                        <div class="card__body">
                            <div class="grid">
                                <div class="col-4">
                                    <div class="card util_border-solid">
                                        <div class="card__body util_d-flex" style="gap:10px">
                                            <span class="icon icon-document-text-solid"
                                                style="font-size: 40px; color: var(--qn-primary)"></span>
                                            <div>
                                                <h3>User Guide Pendaftaran PMB</h3>
                                                <x-core::button type="button" variant="link" href="#"
                                                    style="justify-content:left;border:none">
                                                    Lihat
                                                </x-core::button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
