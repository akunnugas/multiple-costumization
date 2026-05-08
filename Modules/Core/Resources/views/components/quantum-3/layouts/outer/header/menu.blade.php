@props([
    'data' => [],
    'withFormHeader' => false
])

<div @class([
    'p-md-3',
    'py-md-0',
    'px-xl-5',
    'border-bottom',
    'shadow-sm' => !$withFormHeader,
    'bg-white'
])>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg p-0 bg-white">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="w-100 navbar-nav py-1 gap-1">
                    @foreach ($data as $item)
                        @if (!isset($item['items']))
                            <li class="nav-item">
                                <a @class(['nav-link', 'text-primary' => !empty($item['active']) , 'active' => !empty($item['active'])]) aria-current="page" href="{{ url($item['path']) }}">{{ $item['label'] }}</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a @class(['nav-link dropdown-toggle', 'active text-primary' => !empty($item['active'])]) href="#" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    {{ $item['label'] }}
                                </a>
                                <ul class="dropdown-menu">
                                    @foreach ($item['items'] as $sub)
                                        <li><a @class(['dropdown-item', 'active' => !empty($sub['active'])]) href="{{ url($sub['path']) }}">{{ $sub['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </nav>
    </div>
</div>
