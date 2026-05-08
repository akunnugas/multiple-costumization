@props([
    'inline' => true,
    'options' => [],
    'selected' => null,
])

<div data-testid="radio_{{ $attributes['name'] }}">
    @if (empty($selected))
        <input type="hidden" name="{{ $attributes['name'] }}" value="null" />
    @endif
    @foreach ($options as $value => $label)
        @php($id = $attributes['name'] . '_' . strtolower($value))
        <div @class(['form-check', 'form-check-inline' => $inline])>
            <input type="radio" class="form-check-input" id="{{ $id }}" value="{{ $value }}" @checked($value == $selected) {{ $attributes }}>
            <label for="{{ $id }}" class="form-check-label">{{ $label }}</label>
        </div>
    @endforeach
</div>
