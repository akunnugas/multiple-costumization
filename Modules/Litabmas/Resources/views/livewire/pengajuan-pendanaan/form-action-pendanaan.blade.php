<style>
    .container-pendanaan {
        background-color: #EBF4FF;
        border-radius: 8px;
        padding: 20px;
    }

    .row-pendanaan {
        display: flex;
        justify-content: flex-start;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
    }

    .label-pendanaan {
        font-weight: bold;
        color: #333;
        width: 40%;
    }

    .value-pendanaan {
        color: #333;
        text-align: left;
        width: 55%;
    }

    .status-pendanaan {
        color: red;
        font-weight: bold;
    }

    .container-pendanaan div.value-pendanaan:last-child {
        flex: 1;
    }

</style>

<x-core::modal title="Apakah Anda yakin menyatakan lolos pendaanan untuk Proposal ini?" variant="primary" id="modal-confirmation-pendanaan" width="600px" wire:ignore.self>
    <x-core::form method="POST">
        <x-core::modal.body>
            <div class="container-pendanaan">
                <div class="row-pendanaan">
                    <span class="label-pendanaan">ID Registrasi</span>
                    <span class="">:</span>
                    <span class="value-pendanaan">{{ $currentData['kode_registrasi'] }}</span>
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Judul Proposal</span>
                    <span class="">:</span>
                    <span class="value-pendanaan">{{ $currentData['judul_penelitian'] }}</span>
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Periode</span>
                    <span class="">:</span>
                    <span class="value-pendanaan">{{ $currentData['nama_periode_pendanaan'] }}</span>
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Sumber Pendanaan</span>
                    <span class="">:</span>
                    <span class="value-pendanaan">{{ $currentData['nama_sumber_pendanaan'] }}</span>
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Klaster Pendanaan</span>
                    <span class="">:</span>
                    <span class="value-pendanaan">{{ $currentData['nama_klaster'] }}</span>
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Usulan Biaya</span>
                    <span class="">:</span>
                    <span class="value-pendanaan">{{ money($currentData['nominal_anggaran_diajukan'])->format() }}</span>
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Rekomendasi Anggaran</span>
                    <span class="">:</span>
                    @if (count($dataReviewerReviewProposal) > 0)
                        <div>
                            @foreach ($dataReviewerReviewProposal as $item)
                                <div class="value-pendanaan" style="width: 100% !important;">Reviewer {{ $item->reviewer_ke }} - {{ $item->nama_reviewer }} ({{ !empty($item->rekomendasi_anggaran) ? money($item->rekomendasi_anggaran.'00')->format() : 'Tidak Ada' }})</div>
                            @endforeach
                        </div>
                    @else
                        <span class="value-pendanaan">-</span>
                    @endif
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Sisa Anggaran</span>
                    <span class="">:</span>
                    <span class="value-pendanaan">{{ money($currentData['sisa_anggaran'])->format() }}</span>
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Nilai Rata-rata proposal</span>
                    <span class="">:</span>
                    @if ($finalScoreKomposisi)
                        <span class="value-pendanaan">{{ number_format($finalScoreKomposisi, 2) }} <span style="color: {{ $finalScoreKomposisi >= 300 ? 'green' : 'red' }}">({{ $finalScoreKomposisi >= 300 ? 'Lolos' : 'Tidak Lolos' }})</span></span>
                    @else
                        <span class="value-pendanaan">-</span>
                    @endif
                </div>
                <div class="row-pendanaan">
                    <span class="label-pendanaan">Nilai Rata-rata presentasi proposal</span>
                    <span class="">:</span>
                    @if ($finalScorePresentasi)
                        <span class="value-pendanaan">{{ number_format($finalScorePresentasi, 2) }} <span style="color: {{ $finalScorePresentasi >= 300 ? 'green' : 'red' }}">({{ $finalScorePresentasi >= 300 ? 'Lolos' : 'Tidak Lolos' }})</span></span>
                    @else
                        <span class="value-pendanaan">-</span>
                    @endif
                </div>
            </div>

            <div>
                <div style="margin-top: 15px;">
                    <h4>Masukkan biaya yang disetujui</h4>
                    <div class="form-control__group {{ !empty($anggaranError) ? 'error' : '' }}">
                        <input class="form-control__input " style="margin-top: 10px;" format-currency="format-currency" type="text"
                            wire:model.change="records.nominal_anggaran_disetujui" value=""
                            placeholder="Masukkan biaya yang disetujui">
                    </div>
                    @if (!empty($anggaranError))
                        <div class="form-control__error" style="color: red;">{{ $anggaranError }}</div>
                    @endif
                </div>
            </div>

            @php
                $isAlert = $finalScoreKomposisi < 300 || $finalScorePresentasi < 300;
            @endphp

            @if ($isAlert)
                <br>
                <h4>Apakah Anda ingin meloloskan proposal ini dengan hak afirmasi?</h4>
                <div class="form-control__group" style="margin-top: 10px;">
                    <input type="checkbox" wire:model.change="records.is_afirmasi" id="isAfirmasi">
                    <label for="isAfirmasi" style="margin-left: 10px;">Iya, Gunakan Affirmasi</label>
                </div>

                <br>
                <div class="alert alert_warning">
                    <div class="alert__content">
                        <p>Nilai proposal ini di bawah batas minimal untuk lolos pendanaan. Menggunakan hak afirmasi dapat mempengaruhi pendanaan proposal lain yang memenuhi syarat.</p>
                    </div>
                </div>
            @endif

            <x-slot:footer>
                <div class="grid cols-1 cols-sm-2">
                    <x-core::button variant="outline" data-dismiss="modal">
                        Batalkan
                    </x-core::button>

                    <x-core::button variant="primary" class="util_ml-8" wire:click="overview_terimaPendanaan(false)">
                        Ya, Yakin
                    </x-core::button>
                </div>
            </x-slot:footer>
        </x-core::modal.body>
    </x-core::form>
</x-core::modal>

<script>
    document.addEventListener('input', (e) => {
        const target = e.target;
        if (target.getAttribute('format-currency') === 'format-currency') {
            target.addEventListener('keyup', (e) => {
                let value = target.value;
                value = value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                target.value = value;
            });
        }
    });
</script>
