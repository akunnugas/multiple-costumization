@php
    use Modules\Core\Helpers\Page;
@endphp

<x-core::quantum-3.layouts.main :withContainer="false">
    <div class="container-laporan">
        <div class="row row-cols-1 gy-3 p-3 p-lg-4">
            <x-core::quantum-3.layouts.main.header :$menu title="Laporan Kerjasama" />

            <div class="card shadow-sm border-0 rounded-4">
                <div
                    class="card-header d-flex gap-2 align-items-center justify-content-between bg-white border-light-subtle p-3 rounded-top-4 border-2">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="ratio ratio-1x1" style="width: 42px; min-width: 42px;">
                            <span class="d-flex align-items-center justify-content-center rounded-circle p-2 border">
                                <x-core::quantum-3.icon icon="printer-solid" />
                            </span>
                        </div>
                        <div class="d-block ms-1">
                            <h5 class="m-0 text-wrap truncate-2">
                                Cetak {{ __('kerjasama::laporan_kerjasama.main') }}
                            </h5>
                            <span class="fs-6 text-secondary">
                                Anda dapat mencetak laporan kerjasama sesuai dengan filter
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <x-core::quantum-3.form id="filter_laporan" method="POST" action="{{ route('kerjasama.laporan-kerjasama.show') }}">
                        <div class="row row-cols-1 row-cols-md-3 g-3">
                            @foreach ($filters as $filter)
                                @php
                                    $filter['name'] ??= $filter['field'] ?? '';
                                    unset($filter['field']);
    
                                    $attributes = Page::buildAttributes($filter);
                                @endphp
    
                                <x-core::quantum-3.controls.form {{ $attributes }} />
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-end mt-3 gap-2">
                            <x-core::quantum-3.button id="submit_button" leadingIcon="eye">
                                {{ __('kerjasama::laporan_kerjasama.button_cetak_laporan') }}
                            </x-core::quantum-3.button>
                            <x-core::quantum-3.button id="submit_button_blank" leadingIcon="share-02-solid" variant="outline-primary">
                                {{ __('kerjasama::laporan_kerjasama.button_preview') }}
                            </x-core::quantum-3.button>
                        </div>
                    </x-core::quantum-3.form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts') 
        <script>
            const formFilter = document.querySelector('form#filter_laporan');

            const handleSubmit = (ev, isTargetBlank = false) => {
                ev.preventDefault();
                if (isTargetBlank) {
                    formFilter.setAttribute('target', '_blank');
                } else {
                    formFilter.removeAttribute('target');
                }

                formFilter.submit();
            };

            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('submit_button').addEventListener('click', (ev) => handleSubmit(ev));
                document.getElementById('submit_button_blank').addEventListener('click', (ev) => handleSubmit(ev, true));
            });
        </script>
    @endpush

</x-core::quantum-3.layouts.main>