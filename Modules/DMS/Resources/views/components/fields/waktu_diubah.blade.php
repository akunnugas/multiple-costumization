@php
    use Carbon\Carbon;
    use Modules\DMS\Helpers\Format;

    $date = Carbon::parse($data['waktu_diubah']);
    $format = Format::formatDateToRelativeHuman($date);
@endphp

<p>{{$format}}</p>
