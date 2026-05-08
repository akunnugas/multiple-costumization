<?php

namespace Modules\DMS\Helpers;

use Modules\Gate\Models\Modul;

class FolderStructure {
    const SPMI = Modul::CODE_SPMI;
    const SPMI_PENJAMINAN_MUTU = 'spmi_penjaminan_mutu';
    const SPMI_SURAT_KEPUTUSAN = 'spmi_surat_keputusan';
    const SPMI_SURAT_TUGAS = 'spmi_surat_tugas';
    const SPMI_INSTRUMEN_PENGISIAN = 'spmi_instrumen_pengisian';
    const SPMI_PANDUAN_PENILAIAN = 'spmi_panduan_penilaian';
    const SPMI_BERITA_ACARA = 'spmi_berita_acara';
    const SPMI_DOKUMEN_PENGISIAN = 'spmi_dokumen_pengisian';

    const PMB = Modul::CODE_PMB;
    const PMB_PENGUMUMAN = 'pmb_pengumuman';

    const LITABMAS_PETUNJUK_TEKNIS = 'litabmas_petunjuk_teknis';
    const LITABMAS_PENGAJUAN_PENDANAAN_PROPOSAL = 'litabmas_pengajuan_pendanaan_proposal';
    const LITABMAS_PENGAJUAN_PENDANAAN_RAB = 'litabmas_pengajuan_pendanaan_rab';
    const LITABMAS_PENGAJUAN_PENDANAAN_BUKU_TABUNGAN = 'litabmas_pengajuan_pendanaan_buku_tabungan';
    const LITABMAS_PENILAIAN_ADMINISTRASI_SIMILARITY = 'litabmas_penilaian_administrasi_similarity';
    const LITABMAS_PENILAIAN_ADMINISTRASI_AI = 'litabmas_penilaian_administrasi_ai';
    const LITABMAS_PENILAIAN_ADMINISTRASI_REVIEWER_SK = 'litabmas_penilaian_administrasi_reviewer_sk';
    const LITABMAS_PENILAIAN_ADMINISTRASI_PEMBIMBING_SK = 'litabmas_penilaian_administrasi_pembimbing_sk';
    const LITABMAS_AKTIVITAS_PENELITIAN_PENDUKUNG = 'litabmas_aktivitas_penelitian_pendukung';
    const LITABMAS_AKTIVITAS_PENELITIAN_PENDUKUNG_FEEDBACK = 'litabmas_aktivitas_penelitian_pendukung_feedback';
    const LITABMAS_PENGAJUAN_PENDANAAN_LAPORAN_PROGRESS = 'litabmas_pengajuan_pendanaan_laporan_progress';
    const LITABMAS_PENGAJUAN_PENDANAAN_LAPORAN_OUTPUT = 'litabmas_pengajuan_pendanaan_laporan_output';
    const LITABMAS_PENGAJUAN_PENDANAAN_LAPORAN_AKHIR = 'litabmas_pengajuan_pendanaan_laporan_akhir';
    const LITABMAS_PENGUMUMAN_PENDANAAN = 'litabmas_pengumuman_pendanaan';
    const LITABMAS_PENGAJUAN_PENDANAAN_PENELITI_SK = 'litabmas_pengajuan_pendanaan_peneliti_sk';
    const LITABMAS_KLASTER_PENDANAAN_TEMPLATE_RAB = 'litabmas_klaster_pendanaan_template_rab';

    const KERJASAMA = 'kerjasama';
    const KERJASAMA_MITRA = 'kerjasama_mitra';
    const KERJASAMA_DATA = 'kerjasama_data';
    const KERJASAMA_KEGIATAN = 'kerjasama_kegiatan';
}
