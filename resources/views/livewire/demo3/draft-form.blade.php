<div class="kt-container-fluid">
    <div class="grid gap-5 lg:gap-7.5 max-w-4xl">
        <div class="kt-card min-w-full">
            <div class="kt-card-header">
                <h3 class="kt-card-title">
                    {{ $draft ? 'Edit draft' : 'Draft baru' }}
                </h3>
                <div class="flex gap-2">
                    <a class="kt-btn kt-btn-sm kt-btn-outline" href="{{ route('demo3.drafts.index') }}" wire:navigate>
                        Kembali
                    </a>
                </div>
            </div>
            <form class="kt-card-content flex flex-col gap-5" wire:submit="save">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-foreground" for="draft-title">Judul</label>
                    <input
                        class="kt-input"
                        id="draft-title"
                        type="text"
                        wire:model="title"
                        placeholder="Judul draft"
                    />
                    @error('title')
                        <span class="text-sm text-destructive">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-foreground" for="draft-body">Isi</label>
                    <textarea
                        class="kt-textarea min-h-[160px]"
                        id="draft-body"
                        wire:model="body"
                        placeholder="Tulis isi di sini…"
                    ></textarea>
                    @error('body')
                        <span class="text-sm text-destructive">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-foreground" for="draft-status">Status</label>
                    <select class="kt-select w-full max-w-xs" id="draft-status" wire:model="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                    @error('status')
                        <span class="text-sm text-destructive">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button class="kt-btn kt-btn-primary" type="submit">
                        Simpan
                    </button>
                    @if ($draft)
                        <button
                            class="kt-btn kt-btn-outline kt-btn-destructive"
                            type="button"
                            wire:click="delete"
                            wire:confirm="Hapus draft ini permanen?"
                        >
                            Hapus
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
