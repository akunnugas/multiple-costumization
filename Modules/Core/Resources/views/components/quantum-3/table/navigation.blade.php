@props([
    'data' => null,
    'gotoPage' => 'gotoPage',
    'nextPage' => 'nextPage',
    'previousPage' => 'previousPage',
    'setPerPage' => 'setPerPage',
])

<div class="d-flex flex-column-reverse flex-md-row align-items-center justify-content-between gap-3 pt-4 pt-md-0">
    <div class="w-100">
        <p class="text-dark m-0 text-unwrap text-center text-md-start">
            Menampilkan <span class="fw-bold">{{ Format::number($data->firstItem) }}-{{ Format::number($data->lastItem) }}</span> dari
            total {{ Format::number($data->total) }} data
        </p>
    </div>
    <x-core::quantum-3.table.pagination :$data />
</div>
