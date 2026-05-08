@php
    $dataCy = $attributes['data-cy'] ?? $attributes['name'] ?? null;
@endphp
<div class="switch">
    <input type="hidden" name="{{ $attributes['name'] }}" value='0'>
    <input type="checkbox" value='1' {{ $attributes->merge(['class' => 'form-control__switch']) }} @checked(!$value)
        data-cy="{{ $dataCy }}">
    <label for="{{ $attributes['id'] }}" class="form-control__label-switch">
        <span class="check-mark">
            <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.90019 9.85264L11.1375 4.30567C11.3982
                        3.96432 11.8863 3.89898 12.2277 4.15974C12.569 4.4205 12.6344 4.9086 12.3736
                        5.24996L7.62054 11.4721C7.34335 11.835 6.81466 11.8821 6.47765 11.574L3.45297
                        8.80861C3.13595 8.51876 3.11392 8.02679 3.40377 7.70977C3.69362 7.39275 4.18559 7.37072
                        4.50261 7.66057L6.90019 9.85264Z" fill="#0F6AF5">
                </path>
            </svg>
        </span>
        {{-- {{ $attributes['label'] }} --}}
    </label>
</div>
