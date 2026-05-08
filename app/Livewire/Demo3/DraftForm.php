<?php

namespace App\Livewire\Demo3;

use App\Models\Draft;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.demo3.base')]
class DraftForm extends Component
{
    public ?Draft $draft = null;

    public string $title = '';

    public string $body = '';

    public string $status = 'draft';

    public function mount(?Draft $draft = null): void
    {
        $this->draft = $draft;
        if ($draft) {
            $this->title = $draft->title;
            $this->body = (string) ($draft->body ?? '');
            $this->status = $draft->status;
        }
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->draft) {
            $this->draft->update($validated);
        } else {
            Draft::create($validated);
        }

        $this->redirect(route('demo3.drafts.index'), navigate: true);
    }

    public function delete(): void
    {
        if ($this->draft) {
            $this->draft->delete();
            $this->redirect(route('demo3.drafts.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.demo3.draft-form');
    }
}
