@props([
    'data' => [],
    'grid' => true,
])
@php
    $offset = 0;
    $length = ceil(count($data) / 2);
    $number = 1;
@endphp
@if ($grid)
    <div class="grid util_mt-10">
        @while ($items = array_slice($data, $offset, $length))
            <div class="col-12 col-lg-6">
                <div class="grid">
                    @foreach ($items as $value)
                        <div class="col-12 col-lg-12">
                            <div class="row-data__value_custom">
                                {{ $number }}. {!! $value !!}
                            </div>
                        </div>

                        @php
                            $number++;
                        @endphp
                    @endforeach
                </div>
            </div>
            @php($offset += $length)
        @endwhile
    </div>
@else
    @foreach ($data as $item)
        <div class="col-12 util_mt-10">
            {{ $loop->iteration }}. {!! $item !!}
        </div>
    @endforeach
@endif
