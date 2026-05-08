<?php

namespace Modules\Gate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EksternalRoleRequiredBiodata
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $isInternalRole = $user?->apakah_role_internal;
        $hasBiodata = $user?->biodata;

        // jika bukan role internal & tidak memiliki biodata
        if (!$isInternalRole && !$hasBiodata) {
            $callback = $request->client['url_siakad_callback'];
            $errorCode = 'user_role_does_not_have_biodata';
            if (!empty($callback)) {
                return redirect()->away($callback . '?error=' . $errorCode);
            }

            return redirect()->to('/');
        }

        return $next($request);
    }
}
