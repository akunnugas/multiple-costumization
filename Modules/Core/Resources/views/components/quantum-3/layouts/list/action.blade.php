@props([
    'canCreate' => true,
    'canDelete' => true,
    'isReference' => false,
    'withSync' => false,
    'withExport' => false,
    'withImport' => false,
    'showDeleteChecked' => true,
    'createLabel' => null,
    'customCreateLink' => null,
    'syncLabel' => null
])
@php
    use Modules\Core\Helpers\Page;
@endphp

@if ($canDelete && $showDeleteChecked)
    <x-core::quantum-3.button id="button_delete" variant="danger" leading-icon="trash-solid" class="btn_vr-right" :icon="true" />
    <hr class="vr my-1">
@endif
@if ($withSync)
    <x-core::quantum-3.button href="javascript:void(0);" id="btn_sync" leading-icon="arrow-path" >
        <span class="btn__text">{{ $syncLabel ?? 'Sync' }}</span>
    </x-core::quantum-3.button>
@endif
@if ($withExport)
    @php
        $info = Page::showURLInfo();
        $searchParam = request()->query('search');
        $queryParams = Arr::query(['filter' => $info['filter'], 'search' => $searchParam]);
        $exportUrl = Page::buildResourceURL('export') . "?$queryParams";
    @endphp
    <x-core::quantum-3.button :href="$exportUrl" id="button_export" variant="outline-dark" leading-icon="printer" :icon="true" />
@endif

@if ($withImport)
@php
$importUrl = Page::buildResourceURL('import');
$info = Page::showURLInfo();
@endphp
<x-core::quantum-3.button
    id="button_import"
    variant="success"
    leading-icon="upload"
    :icon="true"
    data-bs-toggle="modal"
    data-bs-target="#importModal"
/>
    <x-core::quantum-3.modal id="importModal" title="Import Data {{ ucwords(strtolower($info['resource'])) }}">
    <form action="{{ $importUrl }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <label class="form-label"> Silahkan Unduh Terlebih dahulu format file import data
            </label>
            <br><a href="{{ Page::buildResourceURL('export-format');}}" target="_blank" >Download</a>
            <br><br>
            <input type="file" name="import" required class="form-control" accept=".xlsx,.xls,.csv">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-primary">Import</button>
        </div>
    </form>
</x-core::quantum-3.modal>
@endif
@if ($canCreate)
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

    <x-core::quantum-3.button class="d-flex" {{ $attributes }}>
        <i class="sym sym-plus"></i>

        <span class="d-none d-lg-block">
            {{ $createLabel ?? 'Tambah Data' }}
        </span>
    </x-core::quantum-3.button>
@endif
