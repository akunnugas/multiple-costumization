@props([
    'title' => null,
])
@php
    // default title
    $title ??= $resourceTitle;
@endphp
<x-core::layouts.html :$title>
    @push('head')
        @vite('Modules/Core/Resources/assets/sass/reports/report.scss')
    @endpush
    <main class="main">
        <div class="form-nav no-print">
            <div class="form-nav__left">
                <p>{{$title}}</p>
            </div>
            <div class="form-nav__right">
                <div class="form-nav__wrapper">
                    <div class="form-nav__button-wrapper">
                        <button type="button" id="print" class="btn btn_primary">
                            <span class="icon icon-printer"></span>
                            Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="body">
            <div class="not-found">
                Tidak ada data
            </div>
        </div>
        @pushOnce('scripts')
        <script>
        // print 
        document.getElementById('print').addEventListener('click', function() {
            window.print();
        });
        </script>
        @endPushOnce
    </main>
</x-core::layouts.html>


