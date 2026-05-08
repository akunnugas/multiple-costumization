<?php

namespace Modules\SPMI\Helpers;

use Illuminate\Support\Facades\Http;

class AccreditationSync
{
    private $baseUrl;
    private $token;

    const LembagaAkreditasi = 'lembagaakreditasi';
    const AkreditasiBuku = 'jenisbuku';
    const LEVELACCESS = 'levelakses';
    const PengisianPanduan = 'panduan-akreditasi';
    const INDICATORPERFORMANCEREPORT = 'indikator-lk';
    const INDICATORSELFEVALUATION = 'indikator-led';
    const INDICATORCOLUMN = 'indikator-column';
    const INDICATORROW = 'indikator-row';
    const INDICATORCELL = 'indikator-cell';
    const INDICATORFOOTER = 'indicator-footer';

    const FORM_FXA = "FXA";
    const FORM_FIR = "FIR";
    const FORM_FBC = "FBC";
    const FORM_FCC = "FCC";
    const FORM_FCM = "FCM";
    const FORM_FFR = "FFR";
    const FORM_FBR = "FBR";
    const FORM_FRC = "FRC";
    const FORM_FTA = "FTA";
    const FORM_FKM = "FKM";
    const FORM_FCT = "FCT";

    public function __construct()
    {
        $this->baseUrl = env('ACCREDITATION_BASE_URL_API');
        $this->token = env('ACCREDITATION_TOKEN_API');
    }

    /**
     * FORM TYPE FROM ACCREDITATION API.
     */
    const FORM_TYPE = [
        SELF::FORM_FXA => "Form Textarea",
        SELF::FORM_FIR => "Form Input",
        SELF::FORM_FBC => "Form Row Category",
        SELF::FORM_FCC => "Form Column Category",
        SELF::FORM_FCM => "Form Column Category Input Manual",
        SELF::FORM_FFR => "Form Fixed Row (New)",
        SELF::FORM_FBR => "Form Fixed Row (Old)",
        SELF::FORM_FRC => "Form Fixed Row Category",
        SELF::FORM_FTA => "Form Fixed Row Tahun Akademik",
        SELF::FORM_FKM => "Form Kohort Mahasiswa",
        SELF::FORM_FCT => "Form Custom (Harus dibuat di Helper)"
    ];


    /**
     * Get the list of API.
     *
     * @return array<string>
     */
    public function getListAPI()
    {
        return [
            self::LembagaAkreditasi => ['label' => 'Lembaga Akreditasi', 'function' => 'getAgency'],
            self::AkreditasiBuku => ['label' => 'Jenis Buku', 'function' => 'getBook'],
            self::LEVELACCESS => ['label' => 'Level Akses', 'function' => 'getLevelAccess'],
            self::PengisianPanduan => ['label' => 'Panduan Akreditasi', 'function' => 'getGuide'],
            self::INDICATORPERFORMANCEREPORT => ['label' => 'Indikator LK', 'function' => 'getIndicator'],
            self::INDICATORSELFEVALUATION => ['label' => 'Indikator LED', 'function' => 'getIndicatorLED'],
            self::INDICATORCOLUMN => ['label' => 'Indikator Column'],
            self::INDICATORROW => ['label' => 'Indikator Row'],
            self::INDICATORCELL => ['label' => 'Indikator Cell'],
            self::INDICATORFOOTER => ['label' => 'Indikator Footer'],

        ];
    }

    /**
     * Get the accreditation agency from Accreditation API.
     *
     * @return array
     */
    public function getAgency()
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'ms_lembagaakreditasi';

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);
        return $response;
    }

    /**
     * Get the accreditation books from Accreditation API.
     *
     * @return array
     */
    public function getBook()
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'ms_jenisbuku';

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);
        return $response;
    }

    /**
     * Get the level access from Accreditation API.
     */
    public function getLevelAccess()
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'ms_levelakses';

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);
        return $response;
    }

    /**
     * Get the Guide from Accreditation API.
     *
     * @return array
     */
    public function getGuide($idlembaga = null, $arr_kodebanpt = [])
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'panduan-akreditasi';
        if (!empty($idlembaga)) {
            $headers['idlembaga'] = $idlembaga;
        }

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);

        foreach ($response['data'] as $k => $row) {
            if (!empty($arr_kodebanpt)) {
                if (!in_array($row['kodebanpt'], $arr_kodebanpt)) {
                    unset($response['data'][$k]);
                }
            }
        }

        array_values($response['data']);

        return $response;
    }

    /**
     * Get the Indicator from Accreditation API. (LK)
     *
     * @param string $kodepanduan
     * @return array
     */
    public function getIndicator($kodepanduan)
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'butir-lk';
        $headers['kodepanduan'] = $kodepanduan;

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);
        return $response;
    }

    /**
     * Get the Indicator from Accreditation API. (LED)
     *
     * @param string $kodepanduan
     * @return array
     */
    public function getIndicatorLED($kodepanduan)
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'butir-led';
        $headers['kodepanduan'] = $kodepanduan;

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);
        return $response;
    }

    /**
     * Get the accreditation books from Accreditation API.
     *
     * @return array
     */
    public function getPenilaianPanduan()
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'panduan-penilaian';

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);
        return $response;
    }

    /**
     * Get assessment matrix from Accreditation API.
     *
     * @return array
     */
    public function getAssessmentMatrices($assessmentGuideCode)
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'matriks-penilaian';
        $headers['kodepanduan'] = $assessmentGuideCode;

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);
        return $response;
    }

    /**
     * Get accreditation requirement from Accreditation API.
     *
     * @return array
     */
    public function getAkreditasiSyarat($assessmentGuideCode)
    {
        $url = $this->baseUrl;

        $headers = [];
        $headers['token'] = $this->token;
        $headers['act'] = 'syarat-perlu';
        $headers['kodepanduan'] = $assessmentGuideCode;

        $response = Http::acceptJson()->post($url, $headers);
        $response = json_decode($response->body(), true);
        return $response;
    }
}
