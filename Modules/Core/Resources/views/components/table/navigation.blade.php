@props([
    'data' => null,
    'gotoPage' => 'gotoPage',
    'nextPage' => 'nextPage',
    'previousPage' => 'previousPage',
    'setPerPage' => 'setPerPage',
])
<div class="box-table__action">
    <div class="box-table__action-left">
        <p class="box-table__result">
            Menampilkan <b>{{ Format::number($data->firstItem) }}-{{ Format::number($data->lastItem) }}</b> dari
            total {{ Format::number($data->total) }} data
        </p>
    </div>
    <div class="box-table__action-right">
        <div class="form-control form-control_stand-alone">
            @php
                $options = [];
                foreach (\Modules\Core\Helpers\Pagination::showPerPage() as $baris) {
                    $options[$baris] = $baris . ' Baris';
                }
            @endphp
            <x-core::select id="select-navigation" :$options selected="{{ $data->perPage }}"
                wire:change="{{ $setPerPage }}(event.target.value)" />
        </div>
        <x-core::table.pagination :$data :$gotoPage :$nextPage :$previousPage />
    </div>
</div>
@if (empty($isLivewire))
    @pushOnce('scripts')
        <script>
            document.getElementById("select-navigation").addEventListener("change", function() {
                window.location.href = String("{!! Page::buildURL(['page' => null, 'create' => null, 'edit' => null, 'perPage' => '__']) !!}")
                    .replace('__', document.getElementById("select-navigation").value);
            });
        </script>
    @endPushOnce
@endif
