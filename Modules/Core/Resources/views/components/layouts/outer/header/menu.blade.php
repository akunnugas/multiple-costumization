@props([
    'data' => [],
])
<nav class="nav">
    <ul class="nav__list" data-more-text="Lainnya">
        @foreach ($data as $item)
            <li @class(['nav__item', 'active' => !empty($item['active'])])>
                @if (isset($item['items']))
                    <div class="dropdown dropdown_nav">
                        <a class="nav__link" href="#" data-toggle="dropdown">
                            <span>{{ $item['label'] }}</span>
                            <span class="icon icon-chevron-down-mini"></span>
                        </a>
                        <div class="dropdown__box">
                            <ul class="dropdown__list">
                                @foreach ($item['items'] as $sub)
                                    @if(isset($sub['separator']))
                                        <hr class="divider util_my-4px">
                                        @continue
                                    @endif
                                    <li @class(['dropdown__item', 'active' => !empty($sub['active'])])>
                                        <a href="{{ url($sub['path']) }}">{{ $sub['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <a class="nav__link" href="{{ url($item['path']) }}">
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</nav>

@pushonce('head')
    <style>
        .util_my-4px {
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
        }
    </style>
@endpushonce
