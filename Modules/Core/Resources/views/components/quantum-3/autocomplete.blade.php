@props([
    'type' => 'text',
    'options' => [],
])

@php
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

    $mappedOptions = [];
    foreach ($options as $value => $label) {
        $mappedOptions[] = [
            'label' => $label,
            'value' => $value
        ];
    }
    
    $livewireModel = $attributes->whereStartsWith('wire:model')->first();
@endphp

<div 
    x-data="autoComplete({ 
        options: @js($mappedOptions), 
        model: @js($livewireModel), 
        componentId: @js($attributes['id']),
    })" 
    data-label="{{ $attributes['queryInitial'] ?? '' }}"
    class="position-relative"
>
    <input {{ $attributes->only(['wire:model', 'wire:model.change']) }}  hidden />
    <input 
        {{ $attributes->except(['wire:click', 'wire:model', 'wire:model.change'])->merge(['type' => $type])->class(['form-control']) }} 
        x-model="query"
        x-on:input.debounce.300ms="onInput"
        x-on:keydown.arrow-down.prevent="highlightNext"
        x-on:keydown.arrow-up.prevent="highlightPrev"
        x-on:keydown.enter.prevent="() => select()"
        x-on:blur="close"
        wire:model="{{ $livewireModel . '-label' }}"
        autocomplete="off"
    />  

    <div 
        x-show="open && options.length > 0" 
        x-transition
        class="position-absolute w-100 bg-white border mt-1 rounded shadow z-1"
    >
        <template x-for="(option, index) in options" :key="index">
            <div 
                x-text="option.label"
                @click="select(index)"
                :class="{
                    'bg-primary text-white': index === highlightedIndex,
                    'px-3 py-2 cursor-pointer autocomplete__item': true
                }"
            ></div>
        </template>
    </div>
</div>
