<div class="">
    <div class="">
        <x-core::quantum-3.controls.select :$label purpose="form" {{ $attributes }} />
    </div>
    <div class="pt-1">
        <a wire:key="tambah-mitra-button" wire:click="tambahMitra" style="text-decoration: none;color: var(--qn-semantic-color-link-default)">
            <x-core::quantum-3.icon icon="plus" /> 
            Tambah Data Mitra
        </a>
    </div>
</div>
