<?php

namespace Modules\SPMI\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\SPMI\Models\DokumenHasilAudit;
use Modules\SPMI\Models\HasilAkhirAudit;
use Modules\SPMI\Models\JadwalAudit;
use Modules\SPMI\Policies\DokumenHasilAuditPolicy;
use Modules\SPMI\Policies\HasilAkhirAuditPolicy;
use Modules\SPMI\Policies\JadwalAuditPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        JadwalAudit::class => JadwalAuditPolicy::class,
        HasilAkhirAudit::class => HasilAkhirAuditPolicy::class,
        DokumenHasilAudit::class => DokumenHasilAuditPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
