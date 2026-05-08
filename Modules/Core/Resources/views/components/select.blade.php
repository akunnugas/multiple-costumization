@props([
    'label' => null,
    'options' => [],
    'disabledItems' => [],
    'selected' => null,
    'style' => 'default',
    'variant' => null,
    'isEmpty' => null,
])

@php
    $dataCy = $attributes['data-cy'] ?? $name ?? null;
    if ($dataCy) {
        $attributes['data-cy'] = $dataCy;
    }

    if (isset($attributes['badge'])) {
        unset($attributes['badge']);
    }

    $attributes = Page::buildAttributes(attributes: $attributes, isLivewire: $isLivewire ?? null);
    $variant ??= $style;
    $noClassDefault = $attributes['no_class_default'] ?? null;
    unset($attributes['no_class_default']);
@endphp

<select {{ $attributes->class([
    'select-' . $variant => empty($noClassDefault),
]) }}>
    @if ($slot->isEmpty())
        @if (!empty($label))
            <option selected @if (!$isEmpty) disabled @endif value="null_filter">{{ $label }}</option>
        @endif
        @foreach ($options as $value => $text)
            @php
                $disabled = in_array($value, $disabledItems);
            @endphp
            <option value="{{ $value }}" @selected($value == $selected) @disabled($disabled)>
                {!! strip_tags($text) !!}</option>
        @endforeach
    @else
        {{ $slot }}
    @endif
</select>
@pushOnce('head')
    <style>
        /* temporary waiting quamtum */
        .form-control__group.error .choices__inner {
            border: 0.063rem solid var(--qn-danger);
        }
    </style>

    @php
        $authUser = auth()->user();
    @endphp

    @if (config('app.env') !== 'local' && !$authUser->is_internal)
        @php
            $isLiveChatEnabled = in_array($authUser->kode_role, [
                Modules\Gate\Models\Role::ROLE_ADMINPT,
                Modules\Gate\Models\Role::ROLE_ADMIN_PENJAMINAN_MUTU,
                Modules\Gate\Models\Role::ROLE_LITABMAS_ADMIN_LPPM,
                Modules\Gate\Models\Role::ROLE_ADMIN_KERJASAMA
            ]);
        @endphp

        @if ($isLiveChatEnabled)
            @include('core::components.layouts.livechat')
        @endif
    @endif
@endPushOnce
