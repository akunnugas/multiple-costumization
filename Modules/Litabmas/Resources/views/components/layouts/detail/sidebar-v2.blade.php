@props(['title' => null, 'subtitle' => null, 'submenu'])

<div class="sidebar sidebar_within">
    <div class="sidebar__header">
        <h3 class="sidebar__title">
            {{ $title }}
        </h3>

        <div class="sidebar__action">
            <button type="button" class="btn btn_icon">
                <img src="{{ asset('images/icon/icon-layout.svg') }}" alt="">
            </button>
        </div>
    </div>
    <ul class="sidebar__list">
        @if (empty($submenu))
            <li class="sidebar__item">
                <a class="sidebar__link active" href="#">
                    <span class="sidebar__link-text">{{ $subtitle }}</span>
                </a>
            </li>
        @endif
        @foreach ($submenu as $item)
            <hr>
            @foreach ($item['items'] as $sub)
                <li class="sidebar__item">
                    <a @class(['sidebar__link', 'active' => !empty($sub['active'])]) href="{{ url($sub['path']) }}">
                        <span class="sidebar__link-text">{{ $sub['label'] }}</span>
                    </a>
                </li>
            @endforeach
        @endforeach
    </ul>
</div>

@pushOnce('head')
    <style>
        .sidebar__link-text {
            white-space: normal;
            /* Allow the text to wrap */
            word-wrap: break-word;
            /* Break long words if necessary */
            width: 100%;
            /* Ensure the link text uses full width */
        }

        .sidebar_within{
            max-width: 15% !important;
            width: 15% !important;
        }

        .sidebar__item {
            width: 100%;
            /* Ensure each sidebar item takes full width */
            padding: 0;
            /* Remove padding to extend to the full width */
            margin: 0;
            /* Remove any unnecessary margins */
        }

        .sidebar {
            width: 100%;
            /* Ensure the sidebar itself is using full width */
        }

        .sidebar__list {
            width: 100%;
            /* Ensure the list is also full width */
        }
    </style>
@endPushOnce
