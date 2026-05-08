@php
    use Modules\Core\Helpers\Cstr;
@endphp
<a href="{{ route('kerjasama.data-kerjasama.show', $data['original']) }}" target="_blank">
    {{ Cstr::unescapeDeep($data['text']) }}
</a>