@props([
    'title' => null,
    'subtitle' => null,
    'canCreate' => true,
    'isReference' => false,
    'withHandleButton' => true,
    'createLabel' => null,
    'customCreateLink' => null
])

@push('head')
    <style>

        .empty-list__bordered {
            border-width: var(--bs-border-width);
            border-style: solid;
            border-color: rgb(196.8, 198.4, 200);
            border-top: none;
        }

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

<div class="empty-list d-flex flex-column justify-content-center align-items-center p-5" {{ $attributes }}>
    <div class="empty-list__wrapper text-center">
        <div class="empty-list__content d-flex flex-column align-items-center">
            <div class="">
                <img src="{{ asset('images/empty-state.png') }}" class="img-fluid mb-4" width="200px" alt="illustration">
                <h1 class="h4">{!! $title !!}</h1>
                <p class="text-muted">{!! $subtitle !!}</p>
                @if ($withHandleButton && $canCreate)
                    @php
                        $attributes = [];

                        if (!empty($isLivewire)) {
                            $attributes['wire:click'] = 'showCreate';
                        } elseif (!empty($customCreateLink)) {
                            $attributes['href'] = $customCreateLink;
                        } elseif ($isReference) {
                            $attributes['href'] = Page::buildURL(['create' => 1, 'edit' => null]);
                        } else {
                            $attributes['href'] = Page::createURL();
                        }

                        $attributes = Page::buildAttributes($attributes);
                    @endphp
                    <a {{ $attributes }} class="btn btn-outline-primary mt-3" style="max-width:180px;">
                        <span class="sym sym-plus"></span>
                        <span class="btn__text">{{ $createLabel ?? 'Tambah Data' }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
