@php
    use Carbon\Carbon;
@endphp
@if(!empty($data['waktu_pelaksanaan']))
    @php
        $sudahDilaksanakan = Carbon::now()->gt(Carbon::parse($data['waktu_pelaksanaan']));
    @endphp
    <x-core::badge :variant="$sudahDilaksanakan === true ? 'success' : 'warning'" type="outline" size="sm">
        {{ $sudahDilaksanakan === true ? 'Sudah Dilaksanakan' : 'Belum Dilaksanakan' }}
    </x-core::badge>
@endif
