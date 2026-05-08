<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NSMTracker
{
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;
    const STATUS_SUCCESS_WITH_ERROR = 3;

    public function handle(Request $request, Closure $next)
    {
        if ($this->isLivewireRequest($request)) {
            return $next($request);
        }

        $user = Auth::user();

        $kodePt = session('token.id_univ');

        $role = $user?->nama_role;
        $userId = $user?->id;
        $sessionId = Session::getId();

        try {
            $response = $next($request);

            $status = $response->getStatusCode();
            
            if ($status >= 200 && $status < 300) {
                $actionResult = self::STATUS_SUCCESS;
            } elseif ($status >= 400 && $status < 500) {
                $actionResult = self::STATUS_SUCCESS_WITH_ERROR;
            } else {
                $actionResult = self::STATUS_ERROR;
            }

            $action = $request->headers->get("SX-Action");
            if (empty($action)) {
                $method = $request->getMethod();

                $action = match ($method) {
                    'POST' => 'create',
                    'PUT', 'PATCH' => 'update',
                    'DELETE' => 'delete',
                    default => 'view',
                };
            }

            $pageName = $request->headers->get("SX-Page");
            if (empty($pageName)) {
                $pageName = $request->route()?->getName()
                    ?? $request->route()?->uri()
                    ?? 'page';
                $pageName = str_replace(['{', '}', '-', '_'], ' ', $pageName);
                $pageName = trim(ucwords($pageName));
            }

            $response->headers->set("SX-Kode-PT", $kodePt);
            $response->headers->set("SX-Role", $role);
            $response->headers->set("SX-User", $userId);
            $response->headers->set("SX-Session", $sessionId);
            $response->headers->set("SX-Action", "$action $pageName");
            $response->headers->set("SX-Action-Result", $actionResult);

            // Log::info("NSM Tracker", [
            //     'kode_pt' => $kodePt,
            //     'role' => $role,
            //     'user' => $userId,
            //     'session' => $sessionId,
            //     'action' => "$action $pageName",
            //     'action_result' => $actionResult,
            // ]);

            return $response;
        } catch (Throwable $e) {
            $response = response([
                'message' => $e->getMessage(),
            ], 500);

            $response->headers->set("SX-Kode-PT", $kodePt);
            $response->headers->set("SX-Role", $role);
            $response->headers->set("SX-User", $userId);
            $response->headers->set("SX-Session", $sessionId);
            $response->headers->set("SX-Action", 'view error');
            $response->headers->set("SX-Action-Result", self::STATUS_ERROR);

            return $response;
        }
    }

    private function isLivewireRequest(Request $request): bool
    {
        return $request->is('livewire/*')
            || $request->headers->has('X-Livewire')
            || ($request->has('fingerprint') && is_array($request->input('fingerprint')));
    }
}
