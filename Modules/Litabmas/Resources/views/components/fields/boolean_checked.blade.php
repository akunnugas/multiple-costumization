@if($value === true)
    <x-core::icon type="check-circle-solid" />
@else
    <x-core::icon type="x-circle-solid" />
@endif

@pushonce('head')
    <style>
        .icon.icon-x-circle-solid { /* yg check nggk pelru karena udh ada di qn.css nya*/
            color: var(--qn-danger);
            font-size: 1.5rem
        }
    </style>
@endpushonce
