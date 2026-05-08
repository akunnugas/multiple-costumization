<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Modules\Core\Helpers\WebController;
use Modules\Gate\Models\Role;
use Modules\Litabmas\Enums\JenisPendanaanEnum;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\KlasterPendanaanService;

class KlasterPendanaanController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(private KlasterPendanaanService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $header = [
            ['field' => 'nama_periode_pendanaan', 'label' => 'Periode Pendanaan'],
            ['field' => 'nama_klaster', 'label' => 'Nama Klaster Pendanaan'],
            ['field' => 'kode_jenis_pendanaan', 'label' => 'Jenis Pendanaan'],
            ['field' => 'nama_sumber_pendanaan', 'label' => 'Sumber Pendanaan'],
            ['field' => 'maksimal_anggaran', 'label' => 'Batas Pengajuan Dana', 'component' => 'format_currency', 'styleAlign' => 'right'],
            // ['field' => 'nama_output', 'label' => 'Luaran'],
            ['field' => 'waktu_mulai_pendaftaran', 'label' => 'Tanggal Pendaftaran', 'component' => 'date_klaster'],
            ['field' => 'apakah_sudah_publikasi', 'component' => 'status_publikasi_klaster', 'label' => 'Status Klaster']
        ];

        $filter = [
            'id_periode_pendanaan' => [
                'options' => ['' => '-- Semua Periode Pendanaan --'] + PeriodePendanaan::options(),
                'hideLabel' => true,
            ],
            'kode_jenis_pendanaan' => [
                'options' => ['' => '-- Semua Jenis Pendanaan --'] + JenisPendanaanEnum::CODES,
                'hideLabel' => true,
            ],
        ];

        // default sorting by tahun
        if (empty($request->sort)) {
            $request->merge(['sort' => '6', 'sortDesc' => true]);
        }

        $viewData = [
            'showDeleteChecked' => false,
            'showNumber' => true,
            'staticAlert' => [
                'message' => 'Masukkan program pendanaan yang memiliki fokus atau tujuan serupa untuk menentukan batasan anggaran, persyaratan administrasi, maksimal anggota, serta luaran yang akan dihasilkan dalam penerima dana penelitian atau pengabdian serta mengacu pada juknis maupun Surat Keterangan yang berlaku.'
            ]
        ];

        return WebController::index($this->service, $request, $header, $filter, $viewData, model: KlasterPendanaan::class);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id, Request $request)
    {
        //if role dosen or dosen eksternal
        $kodeRole = auth()->user()?->kode_role;
        $isRoleDosen = in_array($kodeRole, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);

        $showBlade = 'litabmas::pages.klaster-pendanaan.show';
        // dosen bisa get aja
        if ($isRoleDosen) {
            //overide show
            $showBlade = 'litabmas::pages.klaster-pendanaan.show-dosen';
        }

        WebController::validate(id: $id);

        $cards = $this->defineFormFields();

        $klasterPendanaan = $this->service->show($id);

        // bidang ilmu & tema
        $bidangIlmuTemaMapping = $this->service->getBidangIlmuDanTemaByIdKlasterPendanaan($id)
            ->groupBy('id_bidang_ilmu');

        // output & outcome
        $outputMapping = $this->service->getOutputWajibByIdKlasterPendanaan($id);
        $outcomeMapping = $this->service->getOutcomeWajibByIdKlasterPendanaan($id);

        // agenda kegiatan
        $agendaMapping = $this->service->getAgendaMapping($id);

        // mapping outcome dan bidang ilmu
        list($outcomeMapping, $bidangIlmuTemaMapping) = $this->service->getFormattedOutcomeAndBidangIlmu($klasterPendanaan, $outcomeMapping, $bidangIlmuTemaMapping);

        $viewData['page_conf'][] = [
            'custom_page' => [
                'component' => 'custom-show',
                'data' => [
                    'klasterPendanaan' => $klasterPendanaan,
                    'bidangIlmuTemaMapping' => $bidangIlmuTemaMapping,
                    'outputMapping' => $outputMapping,
                    'outcomeMapping' => $outcomeMapping,
                    'agendaMapping' => $agendaMapping,
                ]
            ]
        ];

        return WebController::show($this->service, $id, $cards, KlasterPendanaan::class, viewBlade: $showBlade, viewData: $viewData);

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return WebController::destroy($this->service, $id);
    }

    /**
     * Form input.
     * @return array
     */
    private function defineFormFields()
    {
        $fields = [
            ['field' => 'nama_periode_pendanaan', 'label' => 'Periode Pendanaan'],
            ['field' => 'nama_klaster'],
            ['field' => 'kode_jenis_pendanaan', 'control' => 'radio', 'inline' => false],
            ['field' => 'id_sumber_pendanaan'],
            [
                'field' => 'maksimal_anggaran',
                'currency_field' => 'mata_uang',
                'label' => 'Batas Pengajuan Dana'
            ],
            ['field' => 'kategori_klaster', 'label' => 'Kategori Pendanaan'],
            ['field' => 'minimal_anggota', 'options' => KlasterPendanaan::minMemberOptions()],
            ['field' => 'maksimal_anggota', 'options' => KlasterPendanaan::maxMemberOptions()],
            ['field' => 'apakah_bisa_multi_ajuan', 'label' => 'Apakah Bisa Multi Ajuan'],
            ['field' => 'maksimal_ajuan_per_user', 'label' => 'Maksimal Pengajuan Proposal'],
        ];

        //if role dosen or dosen eksternal
        $kodeRole = auth()->user()?->kode_role;
        $isRoleDosen = in_array($kodeRole, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);

        if (!$isRoleDosen) {
            $fields[] =
                [
                    'field' => 'apakah_butuh_approve_semua_anggota',
                    'options' => [1 => 'Ya, Perlu', 0 => 'Tidak Perlu'],
                    'type' => 'select'
                ];
        }

        $fields['id_dokumen_template_rab'] = ['field' => 'id_dokumen_template_rab', 'showSimpleFile' => true, 'label' => 'Dokumen Template RAB'];
        $fields['apakah_sudah_publikasi'] = ['field' => 'apakah_sudah_publikasi', 'type' => 'select', 'label' => 'Status Klaster', 'options' => [true => 'Dibuka', false => 'Draft'], 'badge' => KlasterPendanaan::PUBLIKASI_BADGE];

        return $fields;
    }
}
