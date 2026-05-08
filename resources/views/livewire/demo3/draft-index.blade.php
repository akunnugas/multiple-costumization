<div class="kt-container-fluid">
    <div class="grid gap-5 lg:gap-7.5">
        <div class="kt-card kt-card-grid min-w-full">
            <div class="kt-card-header flex-wrap gap-2">
                <h3 class="kt-card-title text-sm">
                    Draft
                </h3>
                <div class="flex flex-wrap gap-2 lg:gap-5">
                    <div class="flex">
                        <label class="kt-input">
                            <i class="ki-filled ki-magnifier"></i>
                            <input wire:model.live.debounce.300ms="search" placeholder="Cari judul" type="text"/>
                        </label>
                    </div>
                    <a class="kt-btn kt-btn-primary" href="{{ route('demo3.drafts.create') }}" wire:navigate>
                        <i class="ki-filled ki-plus"></i>
                        Buat draft
                    </a>
                </div>
            </div>
            <div class="kt-card-content">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table table-auto kt-table-border">
                        <thead>
                            <tr>
                                <th class="min-w-[200px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Judul</span>
                                    </span>
                                </th>
                                <th class="min-w-[120px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Status</span>
                                    </span>
                                </th>
                                <th class="min-w-[140px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Diperbarui</span>
                                    </span>
                                </th>
                                <th class="w-[120px] text-end">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Aksi</span>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($drafts as $row)
                                <tr wire:key="draft-{{ $row->id }}">
                                    <td class="text-sm font-medium text-foreground">
                                        {{ $row->title }}
                                    </td>
                                    <td>
                                        @if ($row->status === 'published')
                                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Published</span>
                                        @else
                                            <span class="kt-badge kt-badge-sm kt-badge-secondary kt-badge-outline">Draft</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary-foreground text-sm">
                                        {{ $row->updated_at?->format('d M Y H:i') }}
                                    </td>
                                    <td class="text-end">
                                        <div class="flex justify-end gap-1">
                                            <a class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" href="{{ route('demo3.drafts.edit', $row) }}" wire:navigate title="Edit">
                                                <i class="ki-filled ki-notepad-edit"></i>
                                            </a>
                                            <button
                                                class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost text-destructive"
                                                type="button"
                                                wire:click="delete({{ $row->id }})"
                                                wire:confirm="Hapus draft ini?"
                                                title="Hapus"
                                            >
                                                <i class="ki-filled ki-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-8 text-center text-secondary-foreground text-sm" colspan="4">
                                        Belum ada draft. Klik &quot;Buat draft&quot; untuk menambah.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
