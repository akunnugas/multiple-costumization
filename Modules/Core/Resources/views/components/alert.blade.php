@props([
    'dismissable' => true,
    'title' => null,
    'size' => null,
    'variant' => 'default',
])

<div {{ $attributes->class(['alert', 'alert_' . $variant => $variant, 'alert_' . $size => $size]) }}>
    <div class="alert__content">
        @if (!empty($title))
            <h4 class="alert__heading">{{ $title }}</h4>
        @endif
        <p>{{ $slot }}</p>
    </div>
    @if (!empty($dismissable))
        <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
    @endif
</div>
