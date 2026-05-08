<?php

namespace Modules\SPMI\Tests\Service;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\SPMI\Services\SpmiDokumenManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\SpmiDokumen;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SpmiDokumenManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = SpmiDokumenManagementService::class;
    protected $auth;

    protected function prepare(): void
    {
        // Setup auth
        $user = User::factory()->create();
        $this->auth = $this->actingAs($user);
    }

    /**
     * Test index to retrieve data
     */
    public function test_index_return_data()
    {
        $this->indexReturnData(new SpmiDokumen);
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SpmiDokumen);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SpmiDokumen);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        Storage::fake('local');
        $data = SpmiDokumen::factory()->make()->toArray();

        // Mocking file dokumen
        $data['file'] = UploadedFile::fake()->image('document.pdf');
        $stored = $this->service->store($data);

        // Assert store dokumen
        $this->assertDatabaseHas($stored->getTable(), Arr::except($stored->toArray(), ['waktu_dibuat', 'waktu_diubah']));
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SpmiDokumen, [
            [
                'key' => 'nama_spmi_dokumen',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SpmiDokumen);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SpmiDokumen);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SpmiDokumen);
    }
}
