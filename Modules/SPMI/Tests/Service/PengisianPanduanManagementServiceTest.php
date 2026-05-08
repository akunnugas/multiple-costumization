<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\PengisianPanduanManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\PengisianPanduan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;
use Illuminate\Support\Facades\Storage;
use Modules\SPMI\Models\InternalDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Modules\Core\Models\UnitKerja;

class PengisianPanduanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = PengisianPanduanManagementService::class;
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
        $this->indexReturnData(new PengisianPanduan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PengisianPanduan, 'nama_pengisian_panduan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PengisianPanduan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PengisianPanduan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        Storage::fake('local');
        $data = PengisianPanduan::factory()->make()->toArray();

        // create fake organization record
        UnitKerja::factory()->create();

        // Mocking file dokumen
        $data['file'] = UploadedFile::fake()->image('document.pdf');
        $stored = $this->service->store($data);
        
        $this->assertDatabaseHas($stored->getTable(), Arr::except($stored->toArray(), ['waktu_dibuat', 'waktu_diubah']));
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PengisianPanduan, [
            [
                'key' => 'name',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PengisianPanduan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PengisianPanduan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PengisianPanduan);
    }
}
