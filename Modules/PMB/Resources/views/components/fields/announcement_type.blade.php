@php
    $types = \Modules\PMB\Models\Announcement::TYPES;
@endphp

{{ $types[$value] ?? '' }}
