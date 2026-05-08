@props([
    'title' => null,
    'subtitle' => null,
    'canCreate' => true,
    'isReference' => false,
    'withHandleButton' => true,
    'createLabel' => null,
    'createIcon' => 'icon icon-plus',
    'customCreateUrl' => null,
])

@push('head')
    <style>
        .empty-list__inner {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            width: 38%;
            height: 100%;
        }

        .empty-list__inner p {
            text-align: start;
        }

        .empty-list__inner h1 {
            text-align: start;
        }

        .a-link {
            color: var(--qn-primary-400);
            text-decoration: underline;
        }

        .text-responsive {
            font-size: 1rem;
        }

        @media (max-width: 1440px) {
            .text-responsive {
                font-size: .75rem;
            }
        }
    </style>
@endpush

<div class="empty-list" {{ $attributes }}>
    <div class="empty-list__wrapper">
        <div class="empty-list__content" style="align-items:center;">
            <div class="empty-list__inner">
                <img src="{{ asset('images/empty-state.png') }}" width="200px" alt="illustration">
                <h1>{!! $title !!}</h1>
                <p>{!! $subtitle !!}</p>
                @if ($withHandleButton && $canCreate)
                    @php
                        $attributes = [];

                        if (!empty($isLivewire)) {
                            $attributes['wire:click'] = 'showCreate';
                        } elseif ($isReference) {
                            $attributes['href'] = Page::buildURL(['create' => 1, 'edit' => null]);
                        } elseif (!empty($customCreateUrl)) {
                            $attributes['href'] = $customCreateUrl;
                        } else {
                            $attributes['href'] = Page::createURL();
                        }

                        $attributes = Page::buildAttributes($attributes);
                    @endphp
                    <a {{ $attributes }} class="btn btn_outline" style="width: fit-content; margin-top: 20px;">
                        <span class="{{$createIcon}}"></span>
                        <span class="btn__text">{{ $createLabel ?? 'Tambah Data' }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
