<?php

namespace Modules\Litabmas\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Modules\Core\Helpers\WebController;
use Modules\Litabmas\Models\AgendaKegiatan;
use Modules\Litabmas\Models\KlasterPendanaan;
use Modules\Litabmas\Models\PeriodePendanaan;
use Modules\Litabmas\Services\KlasterPendanaanService;
use Modules\Litabmas\Services\PengajuanPendanaanAnggotaService;
use Modules\Litabmas\Services\PengajuanPendanaanService;
use Modules\Litabmas\Services\SumberPendanaanService;

class PengumumanKlasterController extends Controller
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
     * @return Renderable
     */
    public function index()
    {
        $user = auth()->user();

        // $isReviewer = in_array($user->kode_role, [Role::ROLE_DOSEN, Role::ROLE_DOSEN_EKSTERNAL]);
        // $isPimpinan = in_array($user->kode_role, RoleInternal::ROLE_INTERNAL + [
        //     Role::ROLE_LITABMAS_ADMIN_LPPM,
        //     Role::ROLE_LITABMAS_KETUA_LPPM,
        //     Role::ROLE_REKTOR,
        //     Role::ROLE_DEKAN
        // ]);

        return view('litabmas::pages/pengumuman-klaster/index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('litabmas::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $bidangIlmu = $this->service->getBidangIlmuDanTemaByIdKlasterPendanaan($id, true);

        $outputWajib = $this->service->getOutputWajibByIdKlasterPendanaan($id);
        $outcomeWajib = $this->service->getOutcomeWajibByIdKlasterPendanaan($id);

        $agendaMapping = $this->service->getAgendaMapping($id);

        $klasterPendanaan = $this->service->show($id);
        $totalPendanaan = $this->service->getTotalPendanaanByIdKlasterPendanaan($id);

        $sumberPendanaanService = new SumberPendanaanService();
        $sumberPendanaan = $sumberPendanaanService->show($klasterPendanaan->id_sumber_pendanaan)->toArray();
        $periodePendanaan = PeriodePendanaan::find($sumberPendanaan['id_periode_pendanaan']);
        $pengelolaBantuan = $sumberPendanaanService->getPengelolaBantuan($klasterPendanaan->id_sumber_pendanaan);

        $infoKetua = (new PengajuanPendanaanAnggotaService())->cekMaksimalMendaftarSebagaiKetua($sumberPendanaan['id_periode_pendanaan']);
        $isLimitKetua = !empty($infoKetua['isFull']);

        // cek maksimal ajuan per user untuk klaster ini
        $idBiodata = auth()->user()?->biodata?->id;
        $multiSubmissionCheck = (new PengajuanPendanaanService())->checkMultiSubmissionEligibility($id, $idBiodata);
        $isLimitAjuan = !$multiSubmissionCheck['can_submit'];
        $currentAjuan = $multiSubmissionCheck['current_count'];
        $maxAjuan = $multiSubmissionCheck['max_count'];
        $messageLimit = $multiSubmissionCheck['message'];

        $viewData = [
            'headerAddOn' => $this->service->getHeaderKlasterPenelitian($id),
            'klasterPendanaan' => $klasterPendanaan,
            'bidangIlmu' => $bidangIlmu,
            'outputWajib' => $outputWajib,
            'outcomeWajib' => $outcomeWajib,
            'agendaMapping' => $agendaMapping,
            'pengelolaBantuan' => $pengelolaBantuan,
            'totalPendanaan' => $totalPendanaan,
            'periodePendanaan' => $periodePendanaan,
            'isLimitKetua' => $isLimitKetua,
            'maxKetua' => $infoKetua['max'],
            'isLimitAjuan' => $isLimitAjuan,
            'currentAjuan' => $currentAjuan,
            'maxAjuan' => $maxAjuan,
            'messageLimit' => $messageLimit,
            'title' => 'Detail Klaster Penelitian'
        ];
        return WebController::show($this->service, $id, model: KlasterPendanaan::class, viewData: $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('litabmas::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    //define header
    private function defineFormFields()
    {
        return [
            ['field' => 'nama_klaster', 'label' => 'Nama Klaster'],
            ['field' => 'judul_penelitian', 'label' => 'Sumber Pendanaan'],
            ['field' => 'nama_klaster', 'label' => 'Pengelola Pendanaan'],
            ['field' => 'nama_klaster', 'label' => 'Jenis Pendanaan'],
            ['field' => 'nama_klaster', 'label' => 'Maksimum Pengajuan Dana'],
            ['field' => 'nama_klaster', 'label' => 'Kategori Klaster'],
            ['field' => 'nama_klaster', 'label' => 'Anggota'],
            ['field' => 'nama_klaster', 'label' => 'Status'],
            ['field' => 'nama_klaster', 'label' => 'Proposal yang sudah mengajukan'],
        ];
    }
}
