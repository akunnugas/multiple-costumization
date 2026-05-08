<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Shared\KlienCache;

class DomainDistributionService
{
    /**
     * Find client by domain.
     *
     * @param string $domain
     */
    public static function findClient($domain)
    {
        return KlienCache::find($domain);
    }
}
