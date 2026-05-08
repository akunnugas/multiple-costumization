@props([
    'type' => 'text',
])
{{-- @if ($type == 'file')
    <x-core::file {{ $attributes }} />
@else --}}
    {{-- @if ($type == 'email')
        <span data-input-icon="email"></span>
    @endif --}}
    @php
        // wire:click untuk clear span
        $wireClick = $attributes->get('wire:click');

        // set value khusus datetime-local harus 'Y-m-d\TH:i'
        if ($type == 'datetime-local' && !empty($attributes['value'])) {
            $attributes['value'] = \Carbon\Carbon::parse($attributes['value'])->format('Y-m-d\TH:i');
        }

        if ($type == 'number' && isset($attributes['data-number-float'])) {
            $attributes['step'] = '.01';
        }

        $attributes = Page::buildAttributes(attributes: $attributes, isLivewire: $isLivewire ?? null);
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                unset($attributes[$key]);
            }
        }
    @endphp
    <input {{ $attributes->except(['wire:click'])->merge(['type' => $type])->class(['form-control']) }}>
    @php
        $isUseClear = $attributes->get('data-clear') === 'input' ?? true;
        if ($isUseClear) {
            $param = ['data-clear' => 'input'];
        }
        if (!empty($wireClick)) {
            $param['wire:click'] = $wireClick;
        }

        $attributes = Page::buildAttributes($param ?? null, isLivewire: $isLivewire ?? null);
    @endphp

@pushOnce('scripts')
    @vite('Modules/Core/Resources/assets/quantum-3/js/app.js')
@endPushOnce
