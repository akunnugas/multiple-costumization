@props([
    'arr_button' => []
])
<div class="modal-footer">
    @foreach ($arr_button as $key => $btn)
        <button type="{{ $key === 'submit' ? 'submit' : 'button' }}" id="{{ isset($btn['id']) ? $btn['id'] : '' }}" 
        class="{{$btn['class']}}" {!! $btn['attributes'] ?? '' !!}>{{ $btn['name'] }}</button>
    @endforeach
</div>