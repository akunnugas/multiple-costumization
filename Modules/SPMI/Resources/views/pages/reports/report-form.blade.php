<div class="col-12 d-flex">
    <div class="card card_form" id="cetak-dokumen-spmi">
        <div class="card__header">
            <div class="form-header">
                <div class="form-header__wrapper">
                    <div class="form-header__avatar"><span class="icon icon-printer-mini"></span></div>
                    <div class="form-header__information">
                        <h3 class="form-header__title">Cetak Dokumen SPMI</h3>
                        <p class="form-header__subtitle">Cetak Dokumen SPMI</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card__body">
            <form action="{{ route('spmi.reports.document-reports.generate') }}" method="POST" id="form_report">
                @csrf
                <input type="hidden" name="pageback" id="pageback" value="1">

                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="unit" value="{{ $unit }}">
                <input type="hidden" name="id_jadwal_audit" value="{{ $id_jadwal_audit }}">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="using_kop" value="{{ $record['using_kop'] ? 1 : 0 }}">

                <div class="grid cols-1">
                    @foreach ($fields as $item)
                        @php
                            $item['field'] ??= ($item['name'] ?? null);
                            $attributes = Modules\Core\Helpers\Page::buildAttributes($item);

                            $isDynamic = ($item['field'] === 'id_jadwal_audit');
                        @endphp

                        <div
                            @if(!$isDynamic) wire:ignore @endif
                            wire:key="container-{{ $item['field'] }}-{{ $isDynamic ? count($listJadwalAudit) : 'static' }}"
                        >
                            <x-core::controls.form {{ $attributes }} />
                        </div>
                    @endforeach
                </div>

                <div class="footer-btn pull-right d-flex mt-4">
                    <x-core::button type="submit" variant="primary" leadingIcon="eye" onclick="submitForm(1, '')">
                        Tampilkan
                    </x-core::button>
                    <x-core::button type="submit" variant="primary" leadingIcon="arrow-top-right-on-square" onclick="submitForm(0, '_blank')">
                        Lihat di Tab Baru
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function submitForm(pageback, target) {
            document.getElementById('pageback').value = pageback;
            document.getElementById('form_report').target = target;
        }

        document.addEventListener('livewire:initialized', () => {
            const initSelect2 = (selector) => {
                $(selector || '.select-search, .select-default, .form-select').each(function() {
                    const el = $(this);
                    if (!el.data('select2')) {
                        el.select2({
                            theme: 'quantum3',
                            dropdownParent: el.closest('.form-control')
                        });

                        el.on('change', function (e) {
                            const name = el.attr('name');
                            if (name) {
                                const val = el.val();
                                const property = (name === 'using_kop') ? 'record.using_kop' : name;
                                @this.set(property, val);
                            }
                        });
                    }
                });
            };

            initSelect2();

            Livewire.on('refresh-select2', () => {
                setTimeout(() => {
                    const ja = $('select[name="id_jadwal_audit"]');
                    if (ja.data('select2')) {
                        ja.select2('destroy');
                    }
                    initSelect2('select[name="id_jadwal_audit"]');
                }, 50);
            });

            Livewire.hook('morph.updated', (el, component) => {
                initSelect2();
            });
        });
    </script>
</div>
