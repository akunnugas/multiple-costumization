<div class="form-control search-bar">
    <div class="form-control__group search-group">
        <x-core::input type="search"
                    placeholder="Cari file Anda"
                    wire:model.live.debounce.300ms="search" wire:click="resetSearch"
                    id="search_input" />

        <div class="card search-results" wire:ignore.self>
            <div class="card__body">
                <div class="loader" wire:loading wire:loading.class="visible">
                    <span class="loader__spinner"></span>
                </div>

                @if(!empty($search) && $data)
                    <div wire:loading.remove>
                        @foreach($data->items as $entry)
                            <x-core::button variant="ghost" :href="route('dms.files.preview', $entry['slug'])">
                                <x-dms::fields.nama :data="$entry" :raw="true" :value="$entry['nama_dokumen']" />
                            </x-core::button>
                        @endforeach

                        @if(empty($data->items))
                            <x-core::handler title="Tidak ada hasil" />
                        @endif
                    </div>
                @else
                    <x-core::handler wire:loading.remove title="Cari file Anda" subtitle="Minimal 3 huruf" />
                @endif
            </div>
        </div>
    </div>
</div>
