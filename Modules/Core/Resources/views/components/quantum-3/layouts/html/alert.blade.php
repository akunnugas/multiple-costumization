@props([
    'data' => null,
])
@php
    $type = 'single';
    if (empty($data)) {
        $alert = session('error');
        if (empty($alert)) {
            $alert = session('success');
        } else {
            $variant = 'danger';
        }

        if (!empty($alert) && is_array($alert)) {
            // ambil data type
            if (!empty($alert[2])) {
                $type = $alert[2];
            }

            [$title, $alert] = $alert;
        }
    } else {
        if (!empty($data['title'])) {
            $title = $data['title'];
        }
        if (!empty($data['type'])) {
            $variant = $data['type'];
            if ($data['type'] == 'error') {
                $variant = 'danger';
            }
        }
        if (!empty($data['message'])) {
            $alert = $data['message'];
        }

        $dismissible = $data['dismissible'] ?? $data['dismissable'] ?? true;
        $isHtml = $data['isHtml'] ?? false;
    }
@endphp
@if (!empty($alert))
    <x-core::quantum-3.alert :type="$type" :title="$title ?? null" :variant="$variant ?? 'success'" :dismissable="$dismissible ?? true"
        {{ $attributes }}>

        @if ($isHtml ?? false)
            {!! $alert !!}
        @else
            {{ $alert }}
        @endif
    </x-core::quantum-3.alert>
@endif
