@props([
    'data' => [],
    'numberToc' => false,
])
<div class="form-table" wire:ignore.self>
    <h4 class="form-table__title">Lengkapi formulir ini</h4>
    <ul class="form-table__list">
        @php
            $stepHead = 1;
            $usingStep = isset($this->steps) && isset($this->stepsAnchor) ? true : false;
        @endphp
        @foreach ($data as $anchor => $title)
            <li
                class="form-table__item @if ($usingStep) {{ Str::slug($anchor) == $this->stepsAnchor[$this->steps] ? 'active' : '' }} @endif">
                <a href="#{{ Str::slug($anchor) }}" class="form-table__anchor"
                    @if ($usingStep) wire:click="nextStep({{ $stepHead++ }}, '{{ Str::slug($anchor) }}')" @endif>
                    @if ($numberToc)
                        {{ $loop->iteration }}.
                    @endif{{ $title }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
