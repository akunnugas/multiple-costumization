@props([
    'data' => null,
])
@php
    if (empty($data)) {
        $alert = session('error');
        if (empty($alert)) {
            $alert = session('success');
        } else {
            $variant = 'danger';
        }

        if (!empty($alert) && is_array($alert)) {
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
    }
@endphp
@if (!empty($alert))
    <x-core::alert :title="$title ?? null" :variant="$variant ?? 'success'" :dismissable="$dismissible ?? true"
        {{ $attributes }}>

        {!! $alert !!}
    </x-core::alert>
@endif
