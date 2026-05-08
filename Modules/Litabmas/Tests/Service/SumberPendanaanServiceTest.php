<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\SumberPendanaanService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\SumberPendanaan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SumberPendanaanServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SumberPendanaanService
     */
    protected $service = SumberPendanaanService::class;
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
        $this->indexReturnData(new SumberPendanaan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SumberPendanaan, 'f.nama_sumber_pendanaan');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SumberPendanaan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SumberPendanaan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SumberPendanaan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SumberPendanaan, [
            [
                'key' => 'nama_sumber_pendanaan',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SumberPendanaan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SumberPendanaan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SumberPendanaan);
    }
}
