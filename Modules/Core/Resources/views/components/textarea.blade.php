@php
    $dataCy = $attributes->get('data-cy') ?? $attributes->get('name') ?? null;
    if ($dataCy) {
        $attributes->offsetSet('data-cy', $dataCy);
    }
@endphp
<textarea {{ $attributes->merge(['class' => 'form-control__input textarea']) }}>{{ $slot }}</textarea>
<span data-clear="input"></span>
