<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\PenilaianPanduanManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\PenilaianPanduan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;
use Illuminate\Support\Facades\Storage;
use Modules\SPMI\Models\InternalDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Modules\Core\Models\UnitKerja;

class PenilaianPanduanManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PenilaianPanduanManagementService
     */
    protected $service = PenilaianPanduanManagementService::class;
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
        $this->indexReturnData(new PenilaianPanduan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PenilaianPanduan, 'nama_penilaian_panduan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PenilaianPanduan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PenilaianPanduan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        Storage::fake('local');
        $data = PenilaianPanduan::factory()->make()->toArray();

        // create fake organization record
        UnitKerja::factory()->create();

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
        $this->updateData(new PenilaianPanduan, [
            [
                'key' => 'nama_penilaian_panduan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PenilaianPanduan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PenilaianPanduan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PenilaianPanduan);
    }
}
