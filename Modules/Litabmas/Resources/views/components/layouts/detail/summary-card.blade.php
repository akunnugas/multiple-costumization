@props([
    'data' => [],
    'variant' => 'default', // default, primary
    'customClassBody' => 'grid',
])
@pushonce('head')
    <style>
        .card__body .card__content label {
            font-size: 12px !important;
            color: #363636 !important;
        }

        @media print {
            body {
                background-color: #ffffff;
            }

            .grid {
                box-sizing: border-box;
                margin: 0 auto;
                width: 100%;
                display: grid;
                grid-auto-flow: dense;
                -moz-column-gap: 1rem;
                column-gap: 1rem;
                row-gap: 1rem;
                grid-template-columns: repeat(12, minmax(0, 1fr));
            }

            .col-lg-3 {
                grid-column: span 3;
            }

            .col-lg-9 {
                grid-column: span 9;
            }

            header.header, footer.footer, aside.sidebar, main.main .main__header {
                display: none;
            }

            .sidebar+.main,
            .sidebar+.main .container,
            .card .card__body,
            .card .card__footer{
                padding: unset;
            }

            .avoid-break-inside {
                page-break-inside: avoid;
            }

            .break-after {
                page-break-after: always;
            }

            .break-before {
                page-break-before: always;
            }
        }
    </style>
@endpushonce
<div class="card card_details-{{ $variant }}">
    <div class="card__body">
        <div class="{{ $customClassBody }}">
            @if ($slot->isEmpty())
                <x-litabmas::layouts.detail.summary-line :$data />
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</div>
