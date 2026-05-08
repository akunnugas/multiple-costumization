@php
    $permission = request()->permission;
    $canCreate = $permission['post'] ?? false;
    $canUpdate = $permission['put'] ?? false;
    $canAction = $canCreate || $canUpdate;
@endphp

<x-core::layouts.list :$data :$header :$filter :$search :$sort :$sortDesc :canCreate="false" :canDelete="false" :$title
    :$subtitle :emptyState="$emptyState ?? []">
    <x-slot:tableHeader>
        <div class="alert alert alert_helper">
            <div class="alert__content">
                <p>
                    Pastikan target capaian disesuaikan dengan hasil <b>Rapat Tinjauan Manajemen (RTM)</b> yang telah disepakati. Jika belum tersedia, gunakan acuan dari periode sebelumnya sebelum menetapkan target capaian baru.
                </p>
            </div>
            <span class="icon icon-x-mark-mini" data-dismiss="alert"></span>
        </div>
        <br>
    </x-slot:tableHeader>
    {{-- // TODO: TEMPORARY HIDDEN, PERLU DISESUAIKAN MELIHAT MULTI JADWAL AUDIT --}}
    @if ($canAction && false)
        <x-slot:customAction>
            <x-core::button data-toggle="modal" data-target="#copy-data" variant="outline" id="btn_copy"
                leading-icon="document-duplicate">
                <span class="btn__text">Salin Data dari Periode Sebelumnya</span>
            </x-core::button>
        </x-slot:customAction>
        <x-slot:outer>
            <div id="copy-data" class="modal">
                <div class="modal__overlay" data-dismiss="modal"></div>
                <div class="modal__wrapper">
                    <x-core::form method="POST" action="{{ route('spmi.target-indikator.copy') }}">
                        <div class="modal__header">
                            <div class="modal__header-wrapper">
                                <h3 class="modal__title">Salin Data Target Capaian dari Periode Sebelumnya</h3>
                            </div>
                            <span class="icon icon-x-mark-mini" data-dismiss="modal"></span>
                        </div>
                        <div class="modal__body">
                            @php
                                $listPeriode = $filter['id_audit_periode']['options'] ?? [];
                                $selectedIdPeriode = $filter['id_audit_periode']['selected'] ?? null;
                                $selectedPeriodeName = $listPeriode[$selectedIdPeriode] ?? null;

                                $disabledItems = [];
                                if (!empty($listPeriode) && !empty($selectedIdPeriode)) {
                                    array_push($disabledItems, $selectedIdPeriode);
                                }

                                $listProdi = $filter['id_unit_kerja']['options'] ?? [];
                                $listProdi = array_filter(
                                    $listProdi,
                                    function ($key) {
                                        return $key !== '-';
                                    },
                                    ARRAY_FILTER_USE_KEY,
                                );
                            @endphp
                            {{-- Type hidden --}}
                            <input type="hidden" name="new_id_audit_periode" id="new_id_audit_periode"
                                value="{{ $selectedIdPeriode }}">
                            <div class="grid">
                                <div class="col-12">
                                    <div class="alert alert alert_helper">
                                        <div class="alert__content">
                                            <p>
                                                Silakan pilih periode AMI dan Unit Kerja yang akan disalin ke periode
                                                <b>{{ $selectedPeriodeName }}</b>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <x-core::controls.form name="old_id_audit_periode" purpose="form"
                                        id="old_id_audit_periode" :options="$listPeriode" control="select" variant="search"
                                        data-search-placeholder="Masukkan Periode AMI" placeholder="Pilih Periode AMI"
                                        :$disabledItems required />
                                </div>
                                <div class="col-12">
                                    <x-core::controls.form name="id_unit_kerja" purpose="form" id="id_unit_kerja"
                                        data-search-placeholder="Masukkan Nama Program Studi"
                                        placeholder="Pilih Nama Program Studi" :options="$listProdi" control="select"
                                        variant="search" required />
                                </div>
                                <div class="col-12">
                                    <x-core::checkbox.control class="check-item" name="with_rtm" id="with_rtm"
                                        label="Terapkan target dari Rencana Tindak Manajemen (RTM)" />
                                </div>
                            </div>
                        </div>
                        <div class="modal__footer">
                            <div class="grid cols-1 cols-sm-2">
                                <button type="button" class="btn btn_outline" data-dismiss="modal">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn_primary">Salin</button>
                            </div>
                        </div>
                    </x-core::form>
                </div>
            </div>
        </x-slot:outer>
    @endif
</x-core::layouts.list>
