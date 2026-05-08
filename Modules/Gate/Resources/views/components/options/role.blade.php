@php
    $options = \Modules\Gate\Models\Role::options();
@endphp
<x-core::controls.select :$options {{ $attributes }} />
