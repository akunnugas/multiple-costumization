<?php

namespace Modules\SPMI\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Modules\Core\Models\UnitKerja;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit;

class KarirLinkAPI
{
    private $url;
    private $token;

    const WAKTU_TUNGGU = '/open-api/graduate-waiting-job';
    const KESESUAIAN_BIDANG = '/open-api/graduate-job-suitability';
    const TINGKAT_TEMPAT_KERJA = '/open-api/graduate-workplace';
    const STATUS_LULUSAN = '/open-api/graduate-status';
    const PENGISIAN_TRACER_STUDI = '/open-api/graduate-submit';
    const DETAIL_LULUSAN = '/open-api/graduate-program-status';

    /**
     * Get the list of API.
     *
     * @return array<string>
     */
    public function getListAPI()
    {
        return [
            self::WAKTU_TUNGGU => ['label' => 'Waktu Tunggu', 'function' => 'getWaktuTunggu'],
            self::KESESUAIAN_BIDANG => ['label' => 'Kesesuaian Bidang', 'function' => 'getKesesuaianBidang'],
            self::TINGKAT_TEMPAT_KERJA => ['label' => 'Tingkat Tempat Kerja', 'function' => 'getTingkatTempatKerja'],
            self::STATUS_LULUSAN => ['label' => 'Status Lulusan', 'function' => 'getStatusLulusan'],
            self::PENGISIAN_TRACER_STUDI => ['label' => 'Pengisian Tracer Studi', 'function' => 'getPengisianTracerStudi'],
            self::DETAIL_LULUSAN => ['label' => 'Detail Lulusan', 'function' => 'getDetailLulusan'],
        ];
    }

    public function getData($path, $params = null)
    {
        $this->url = env('KARIRLINK_BASE_URL_API') . $path;

        // generate token
        $this->token = JWT::encode([
            'kode_pt' => request()->client['kode_dikti'],
            'iat' => time()
        ], env('KARIRLINK_JWT_TOKEN'), 'HS256');

        return $this->{$this->getListAPI()[$path]['function']}($params);
    }

    /**
     * Get the waktu tunggu from KarirLink API
     *
     * @return array
     */
    public function getWaktuTunggu($params = null)
    {
        $headers = [];
        $headers['token'] = $this->token;

        $response = Http::withHeaders($headers)->acceptJson()->post($this->url, [
            'period' => $params['tahun_audit'],
            'id_jenjang' => $params['kode_jenjang'],
            'unit_id' => $params['kode_unit'],
        ]);

        $response = json_decode($response->body(), true);

        return $response;
    }

    /**
     * Get the kesesuaian bidang from KarirLink API
     *
     * @return array
     */
    public function getKesesuaianBidang($params = null)
    {
        $headers = [];
        $headers['token'] = $this->token;

        $response = Http::withHeaders($headers)->acceptJson()->post($this->url, [
            'period' => $params['tahun_audit'],
            'id_jenjang' => $params['kode_jenjang'],
            'unit_id' => $params['kode_unit'],
        ]);
        $response = json_decode($response->body(), true);

        return $response;
    }

    /**
     * Get the kesesuaian bidang from KarirLink API
     *
     * @return array
     */
    public function getTingkatTempatKerja($params = null)
    {
        $headers = [];
        $headers['token'] = $this->token;

        $response = Http::withHeaders($headers)->acceptJson()->post($this->url, [
            'period' => $params['tahun_audit'],
            'id_jenjang' => $params['kode_jenjang'],
            'unit_id' => $params['kode_unit'],
        ]);
        $response = json_decode($response->body(), true);

        return $response;
    }
}
