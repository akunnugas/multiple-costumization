<?php

namespace Modules\Kerjasama\Http\Controllers\Web;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Helpers\Cstr;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\WebController;
use Modules\Core\Helpers\WebRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Modules\Core\Helpers\Page;
use Modules\Kerjasama\Helpers\Menu;
use Modules\Kerjasama\Models\Evaluasi;
use Modules\Kerjasama\Models\JawabanPeserta;
use Modules\Kerjasama\Models\Kegiatan;
use Modules\Kerjasama\Models\Kerjasama;
use Modules\Kerjasama\Models\OpsiJawaban;
use Modules\Kerjasama\Models\PenanggungJawab;
use Modules\Kerjasama\Models\Pertanyaan;
use Modules\Kerjasama\Models\Peserta;
use Modules\Kerjasama\Models\PihakPenanggungJawab;
use Modules\Kerjasama\Services\EvaluasiKerjasamaManagementService;
use Modules\Kerjasama\Services\JawabanManagementService;
use Modules\Kerjasama\Services\KegiatanManagementService;
use Modules\Kerjasama\Services\KerjasamaManagementService;
use Modules\Kerjasama\Services\PesertaManagementService;

class EvaluasiPengisianController extends Controller
{
    /**
     * Create a new controller.
     * @return void
     */
    public function __construct(
        private EvaluasiKerjasamaManagementService $service,
        private JawabanManagementService $jawabanService,
        private PesertaManagementService $pesertaService,
        private KerjasamaManagementService $kerjasamaService
    ) {}

    public function index($uuid)
    {

        if (!Str::isUuid($uuid)) {
            return view('kerjasama::pages.pengisian-kuesioner.index-error');
        }
        $evaluasi = Evaluasi::where('uuid', $uuid)->first();
        if (!$evaluasi) {
            return view('kerjasama::pages.pengisian-kuesioner.index-error');
        }
        if (!$evaluasi->is_published) {
            return view('kerjasama::pages.pengisian-kuesioner.index-error');
        }
        if (now()->gt(\Carbon\Carbon::parse($evaluasi->selesai)->endOfDay())) {
            return view('kerjasama::pages.pengisian-kuesioner.index-error');
        }

        $isScheduled = now()->lt(\Carbon\Carbon::parse($evaluasi->mulai)->startOfDay());


        $evaluasi->load('pertanyaan.opsiJawaban');

        $cards = [
            ...$this->defineFormFields(),
            'informasi-kuesioner' => [
                'title' => 'Soal Kuesioner',
                'subtitle' => 'List Soal Kuesioner',
                'items' => [
                    ['field' => 'soal'],
                ]
            ],
        ];

        $cards['informasi-kerjasama']['title'] = $evaluasi->judul_evaluasi;
        $cards['informasi-kerjasama']['subtitle'] = 'Detail informasi terkait evaluasi';
        $unitKerjaOptions = $this->kerjasamaService->getUnitKerjaOptions();

        return view('kerjasama::pages.pengisian-kuesioner.index', [
            'data' => $cards,
            'rawData' => $evaluasi,
            'unitKerjaOptions' => $unitKerjaOptions,
            'isScheduled' => $isScheduled
        ]);
    }

    public function store(Request $request, $uuid)
    {
        if (!Str::isUuid($uuid)) {
            return view('kerjasama::pages.pengisian-kuesioner.index-error');
        }

        $evaluasi = Evaluasi::where('uuid', $uuid)->first();
        if (!$evaluasi) {
            return view('kerjasama::pages.pengisian-kuesioner.index-error');
        }
        $evaluasi->load('pertanyaan');

        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ];
        $messages = [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'phone.required' => 'Nomor HP wajib diisi.',
        ];
        foreach ($evaluasi->pertanyaan as $pertanyaan) {
            if ($pertanyaan->apakah_wajib) {
                $rules['jawaban.' . $pertanyaan->id] = 'required';
                $messages['jawaban.' . $pertanyaan->id . '.required'] = 'Pertanyaan "' . $pertanyaan->pertanyaan . '" wajib diisi.';
            }
        }
        $request->validate($rules, $messages);


        $dataPeserta = [
            'evaluasi_id' => $evaluasi->id,
            'uuid' => Str::uuid(),
            'email' => $request->email,
            'phone' => $request->phone,
            'nama' => $request->nama,
        ];
        $dataPeserta = WebRequest::sanitizeXSS($dataPeserta, [Peserta::class]);

        $peserta = $this->pesertaService->store($dataPeserta);

        if (Error::isError($peserta)) {
            return $peserta->redirectBack();
        }


        foreach ($request->jawaban as $pertanyaanId => $jawabanValue) {
            $pertanyaan = $evaluasi->pertanyaan->find($pertanyaanId);
            if ($pertanyaan) {
                $dataJawaban = [
                    'peserta_id' => $peserta->id,
                    'pertanyaan_id' => $pertanyaanId,
                ];

                if ($pertanyaan->tipe == 'option') {
                    $dataJawaban['opsi_jawaban_id'] = $jawabanValue;
                    $opsi = $pertanyaan->opsiJawaban->where('id', $jawabanValue)->first();
                    $dataJawaban['jawaban'] = $opsi ? $opsi->jawaban : null;
                } else {
                    $dataJawaban['jawaban'] = $jawabanValue;
                }

                $dataJawaban = WebRequest::sanitizeXSS($dataJawaban, [JawabanPeserta::class]);

                $jawaban = $this->jawabanService->store($dataJawaban);
                if (Error::isError($jawaban)) {
                    return $jawaban->redirectBack();
                }
            }
        }

        return redirect()->route('kerjasama.kuesioner.success', $uuid)->with('kuesioner_success', true);
    }

    public function success($uuid)
    {
        if (!session('kuesioner_success')) {
            return redirect()->route('kerjasama.kuesioner.view', $uuid);
        }
        return view('kerjasama::pages.pengisian-kuesioner.index-success', compact('uuid'));
    }



    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id, $id_parent)
    {
        return WebController::destroy($this->service, $id_parent);
    }

    public function show() {}


    private function defineFormFields()
    {
        return [
            'informasi-kerjasama' => [
                'title' => 'Data Evaluasi',
                'subtitle' => 'Informasi Evaluasi',
                'edit_url' => url()->current() . '/edit',
                'items' => [
                    [
                        'field' => 'judul_evaluasi',
                    ],
                    [
                        'field' => 'tipe_evaluasi',

                    ],
                    [
                        'field' => 'is_published',

                    ],
                    [
                        'field' => 'mulai',

                    ],
                    [
                        'field' => 'selesai',

                    ],
                ],
            ],
        ];
    }

    private function defineFilter()
    {
        return [];
    }
}
