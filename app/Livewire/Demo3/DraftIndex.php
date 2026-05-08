<?php

namespace App\Livewire\Demo3;

use App\Models\Draft;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.demo3.base')]
class DraftIndex extends Component
{
    public string $search = '';

    public function delete(int $id): void
    {
        Draft::query()->whereKey($id)->delete();
    }

    public function render()
    {
        $drafts = Draft::query()
            ->when($this->search !== '', function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->limit(100)
            ->get();

        return view('livewire.demo3.draft-index', [
            'drafts' => $drafts,
        ]);
    }
}
