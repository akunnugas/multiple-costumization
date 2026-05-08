@props([
    'gravity' => 'top',
    'position' => 'center',
    'duration' => 10_000,
    'headless' => false,
    'autoShow' => true,
    'href' => null,
    'action' => null,
])

@pushOnce('head')
    <script type="text/javascript" src="/js/toastify.js"></script>
    @vite('Modules/DMS/Resources/assets/sass/components/toast.scss')

    <script>
        function toast(options = {}) {
            const opt = {
                gravity: 'top',
                position: 'center',
                ...options
            }

            if (typeof opt.html !== 'undefined') {
                opt.text = opt.html;
                opt.escapeMarkup = false;
            }

            const toast = Toastify(opt);

            if (opt.autoShow ?? true) {
                toast.showToast();
            }

            return toast;
        }
    </script>
@endpushOnce

@if (!$headless)
    @php
        $html = $slot->toHtml();

        if (!empty($action)) {
            $actionLink = "<a class=\"toast-action\" href=\"{$action['href']}\">{$action['label']}</a>";
            $html .= $actionLink;
        }
    @endphp

    @if (empty($isLivewire))
        <script type="module">
            toast({
                html: @js($html),
                gravity: @js($gravity),
                position: @js($position),
                duration: @js($duration),
                autoShow: @js($autoShow),
                destination: @js($href)
            });
        </script>
    @else
        @script
            <script type="module">
                toast({
                    html: @js($html),
                    gravity: @js($gravity),
                    position: @js($position),
                    duration: @js($duration),
                    autoShow: @js($autoShow),
                    destination: @js($href)
                });
            </script>
        @endscript
    @endif
@endif

{{-- Check session --}}
@if ($headless && Session::has('toast'))
    @php
        if(Session::has('toast')) {
            $toast = Session::get('toast');

            $message = $toast['message'];
            $destination = $toast['destination'] ?? null;
            $icon = $toast['icon'] ?? null;
            $duration = $toast['duration'] ?? 10_000;
            $gravity = $toast['gravity'] ?? 'top';
            $position = $toast['position'] ?? 'center';
            $action = $toast['action'] ?? null;
        }
    @endphp

    <x-dms::toast
        :gravity="$gravity"
        :position="$position"
        :duration="$duration"
        :href="$destination"
    >
        @if (!empty($icon))
            <x-core::icon :type="$icon" />
        @endif

        {{ $message }}

        @if(!empty($action))
            <a class="toast-action" href={{ $action['href'] }}>{{ $action['label'] }}</a>
        @endif
    </x-dms::toast>
@endif
