<?php

namespace Tests\Feature;

use App\Models\Draft;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Demo3DraftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_drafts_index_loads(): void
    {
        $response = $this->get(route('demo3.drafts.index'));

        $response->assertStatus(200);
        $this->assertStringContainsString('wire:snapshot', $response->getContent());
    }

    public function test_draft_create_and_edit_flow(): void
    {
        $response = $this->get(route('demo3.drafts.create'));
        $response->assertStatus(200);

        $draft = Draft::create([
            'title' => 'Test',
            'body' => 'Body',
            'status' => 'draft',
        ]);

        $response = $this->get(route('demo3.drafts.edit', $draft));
        $response->assertStatus(200);
        $this->assertStringContainsString('Test', $response->getContent());
    }
}
