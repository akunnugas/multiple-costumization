@php
    use Modules\Litabmas\Models\AgendaKegiatan;
    use Modules\Litabmas\Models\PengajuanPendanaan;
    use Modules\Litabmas\Models\PengajuanPendanaanStatus2;
    use Modules\Gate\Models\Role;
@endphp

@if(!empty($item['original']))
    @php
        $text = PengajuanPendanaanStatus2::getLabelStatus($item['original']);
        $colorVariant = PengajuanPendanaanStatus2::getStatusColorVariant($item['original']);

        // custom pengecekan
        if (isset($urlInfo)) {
            $isDosen = in_array(auth()->user()?->kode_role, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);

            $service = new Modules\Litabmas\Services\PengajuanPendanaanService;
            $kodeAgenda = null;

            switch ($item['original']) {
                case PengajuanPendanaanStatus2::LEVEL5_TIDAK_LOLOS_ADMINISTRASI:
                case PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI:
                    $kodeAgenda = AgendaKegiatan::STEP_PENGUMUMAN_ADMINISTRASI;
                    break;
                case PengajuanPendanaanStatus2::LEVEL8_TIDAK_LOLOS_NOMINASI:
                case PengajuanPendanaanStatus2::LEVEL8_LOLOS_NOMINASI:
                    $kodeAgenda = AgendaKegiatan::STEP_PENGUMUMAN_NOMINASI;
                    break;
                case PengajuanPendanaanStatus2::LEVEL10_TIDAK_LOLOS_PENDANAAN:
                case PengajuanPendanaanStatus2::LEVEL10_LOLOS_PENDANAAN:
                    $kodeAgenda = AgendaKegiatan::STEP_PENGUMUMAN_PENDANAAN;
                    break;
                default:
                    break;
            }

            $proposal = PengajuanPendanaan::find($urlInfo['sub_resource_id']);
            if ($kodeAgenda && $isDosen) {
                $agendaPengumuman = $service->getTimelineStatusAgendaKegiatanByIdPengajuanPendanaan($urlInfo['sub_resource_id'], $kodeAgenda);
                $agendaPengumuman = array_shift($agendaPengumuman);

                $isProses = false;
                if ($item['original'] === PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI) {
                    if ($proposal->status_similarity != PengajuanPendanaan::LOLOS_SIMILARITY_AI) {
                        $isProses = true;
                        $text = 'Proses Seleksi Administrasi';
                        $colorVariant = 'warning';
                    }
                }

                if ($agendaPengumuman && !$agendaPengumuman->completed) {
                    // tunda pengumuman untuk dosen
                    if (!$isProses && !$agendaPengumuman->active) {
                        $tTitle = '';
                        if ($kodeAgenda === AgendaKegiatan::STEP_PENGUMUMAN_ADMINISTRASI) {
                            $tTitle = 'Seleksi Administrasi';
                        } elseif ($kodeAgenda === AgendaKegiatan::STEP_PENGUMUMAN_NOMINASI) {
                            $tTitle = 'Seleksi Nominasi';
                        } elseif ($kodeAgenda === AgendaKegiatan::STEP_PENGUMUMAN_PENDANAAN) {
                            $tTitle = 'Seleksi Pendanaan';
                        }
                        $text = 'Proses ' . $tTitle;
                        $colorVariant = 'warning';
                    }
                }
            } else {
                if ($item['original'] === PengajuanPendanaanStatus2::LEVEL5_LOLOS_ADMINISTRASI) {
                    if ($proposal->status_similarity != PengajuanPendanaan::LOLOS_SIMILARITY_AI) {
                        $text = 'Proses Seleksi Administrasi';
                        $colorVariant = 'warning';
                    }
                }
            }
        }
    @endphp

    <x-core::badge :variant="$colorVariant" type="secondary" size="sm">
        {{ $text }}
    </x-core::badge>
@endif
