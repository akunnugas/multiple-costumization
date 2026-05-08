@php
    use Modules\Core\Services\External\NavigationService;

    $navigationService = new NavigationService();
    $appId = $navigationService->appId;
    $url = $navigationService->url;
    $session = $navigationService->getSessionToken();
    $token = $session['token'] ?? null;
    $flush = $session['flush_cache'] ?? false;
@endphp

@if (!empty($token) && !empty($url) && !empty($appId))
    <nav-side token="{{ $token }}" application-id="{{ $appId }}"></nav-side>
    <script type="module" src="{{ $url }}/build/assets/navigation.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.addEventListener("navigation:load", ({
                detail
            }) => {
                detail?.validateModule('{{ auth()->user()->kode_modul }}');
            })
        });
    </script>

    @if ($flush)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                window.addEventListener("navigation:load", ({
                    detail
                }) => {
                    detail?.flushCache();
                })
            });
        </script>
    @endif
@endif
