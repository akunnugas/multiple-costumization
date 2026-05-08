@php
    $infoLevel = $data['info_level'];
debug($data);

    $px = $data['info_level'] * 18 . 'px';
@endphp
<div @style(["margin-left: $px"])>
    {!! $value !!}
</div>
