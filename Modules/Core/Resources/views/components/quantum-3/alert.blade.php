@props([
    'dismissable' => true,
    'title' => null,
    'variant' => 'primary',
    'icon' => 'info-circle',
    'type' => 'single' // single, double
])

<div {{ $attributes->class([
    'alert',
    'alert-' . $variant,
    'alert-dismissible ' => $dismissable,
    'fade show d-flex gap-2',
]) }} role="alert" >
    @if (!empty($icon))        
        <x-core::quantum-3.icon :$icon />
    @endif
    <div class="d-block">
        @if (!empty($title) && $type != 'single')            
            <h6 class="alert-heading">{{ $title }}</h6>
        @endif
        <span>
            {{ $slot }} @if ($type != 'double') {{ $title }} @endif
        </span>
    </div>
 
    @if (!empty($dismissable))
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="z-index: 1;"></button>
    @endif
</div>