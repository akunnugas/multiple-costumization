@props([
    'action' => null,
    'method' => 'POST',
    'withUpload' => true,
    'id' => null,
])

@php($method = strtoupper($method))

<form method="{{ $method == 'GET' ? 'GET' : 'POST' }}" id="{{ $id }}"
    @if (!empty($action)) action="{{ $action }}" @endif
    @if (!empty($withUpload)) enctype="multipart/form-data" @endif {{ $attributes }}>
    @if ($method != 'GET')
        @csrf
    @endif
    @if (!in_array($method, ['GET', 'POST']))
        @method($method)
    @endif
    {{ $slot }}
</form>
