<?php

namespace Modules\Core\Services\SiakadV1;

use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\ServiceReturn;
use Modules\Core\Models\SiakadV1\SettingSimV1Model;

class SettingSimV1ManagementService
{    
    /**
     * Get setting sim value by key
     *
     * @return mixed
     */
    public function get(string $key, array $condition = []): mixed 
    {
        try {
            $rawData = SettingSimV1Model::where('keysetting', $key)
                ->when(!empty($condition), fn ($query) => $query->where($condition))
                ->firstOrFail('valuesetting')->valuesetting;
        } catch (\Throwable $th) {
            return new Error($th->getMessage());
        }
        
        return $rawData;
    }
}
