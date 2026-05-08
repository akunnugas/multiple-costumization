<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\SeleksiNilaiManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SeleksiNilai;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SeleksiNilaiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = SeleksiNilaiManagementService::class;
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
        $this->indexReturnData(new SeleksiNilai);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SeleksiNilai, 'id_pendaftar', null, '=');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SeleksiNilai);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SeleksiNilai);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SeleksiNilai);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SeleksiNilai, [
            [
                'key' => 'keterangan_nilai',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SeleksiNilai);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SeleksiNilai);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SeleksiNilai);
    }
}
