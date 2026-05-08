<?php

namespace Modules\DMS\Helpers;

class DokumenHelper extends FolderStructure {
    public static function generateDocUrl($slug, $version = null) {
        $params['dokumen'] = $slug;

        if (!empty($version)) {
            $params['version'] = $version;
        }
        
        return route('dms.files.raw', $params);
    }
}