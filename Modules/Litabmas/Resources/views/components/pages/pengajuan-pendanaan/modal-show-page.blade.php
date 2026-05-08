@props([
    'apakahKetua',
    'pendaftaranDitutup',
    'statusAgendaKegiatan',
    'idPengajuanPendanaan' => null,
    'statusAnggota' => null,
    'isAnggotaPenelitian' => false,
    'apakahMasihBisaTerimaUndangan' => false,
    'judulPenelitian',
])

@php
    use \Modules\Litabmas\Models\PengajuanPendanaanAnggota;

    $idBiodata = auth()->user()?->biodata?->id;
@endphp

@if(!$pendaftaranDitutup && $apakahKetua)
    @if($statusAgendaKegiatan === \Modules\Litabmas\Enums\StatusAgendaKegiatanEnum::DRAFT)
        <x-core::modal title="Apakah Anda yakin mengajukan proposal ini?" variant="primary" id="modal-kirim-pengajuan">
            <x-core::form method="PUT" action="{{ route('litabmas.pengajuan-pendanaan.ajukan-proposal', $idPengajuanPendanaan) }}">
                <x-core::modal.body>
                    Proposal penelitian dapat berhasil diajukan setelah semua calon anggota dosen menerima tawaran.

                    <x-slot:footer>
                        <div class="grid cols-1 cols-sm-2">
                            <x-core::button variant="outline" data-dismiss="modal">
                                Batal
                            </x-core::button>

                            <x-core::button variant="primary" type="submit">
                                Ajukan Proposal
                            </x-core::button>
                        </div>
                    </x-slot:footer>
                </x-core::modal.body>
            </x-core::form>
        </x-core::modal>
    @else
        <x-core::modal title="Apakah Anda yakin membatalkan pengajuan proposal ini?" variant="primary" id="modal-batal-pengajuan">
            <x-core::form method="PUT" action="{{ route('litabmas.pengajuan-pendanaan.batalkan-proposal', $idPengajuanPendanaan) }}">
                <x-core::modal.body>
                    Proposal penelitian yang belum diajukan tidak akan diproses lebih lanjut oleh Administator.
                    <x-slot:footer>
                        <div class="grid cols-1 cols-sm-2">
                            <x-core::button variant="outline" data-dismiss="modal">
                                Tidak
                            </x-core::button>

                            <x-core::button variant="primary" type="submit">
                                Ya, Batalkan!
                            </x-core::button>
                        </div>
                    </x-slot:footer>
                </x-core::modal.body>
            </x-core::form>
        </x-core::modal>
    @endif
@endif
