<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\DB;

class SiakadV1
{
    /**
     * Request data from siakad v1
     */
    public static function send($sql, $bindings = [])
    {
        $err = false;
        try{
            $data = DB::connection('siakadv1')->select($sql, $bindings);
            $data = json_decode(json_encode($data), true);
        } catch (\Exception $e) {
            $err = true;
            $data = $e->getMessage();
        }

        return [$err, $data];
    }

    /**
     * Set log action berdasarkan kolom siakad v1.
     *
     * @param array $data
     * @return array
     */
    public static function setLogAction(array $data)
    {
        $urlInfo = Page::showURLInfo();
        $urlInfoId = $urlInfo['id'] ?? null;

        $data['t_updateuser'] = auth()->user()->email_user;
        $data['t_updatetime'] = date('Y-m-d H:i:s');
        $data['t_updateip'] = request()->ip();
        $data['t_updateact'] = 'siakadv2_' . $urlInfo['module'] . '_' . $urlInfo['resource']
            . ($urlInfoId ? '_' . $urlInfoId : '');

        return $data;
    }

    /**
     * Get data from siakad v1 (view)
     */
    public static function getDataFromView($view, $filter = [], $select = '*', $order = '') {
        $sql = "select $select from " . $view;
        if (!empty($filter)) {
            $filter = Cstr::setCondition($filter);
            $sql .= $filter;
        }

        if (!empty($order)) {
            $sql .= " order by $order";
        }

        return self::send($sql);
    }
}
