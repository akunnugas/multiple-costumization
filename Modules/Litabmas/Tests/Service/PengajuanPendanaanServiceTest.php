<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\PengajuanPendanaanService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\PengajuanPendanaan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PengajuanPendanaanServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PengajuanPendanaanService
     */
    protected $service = PengajuanPendanaanService::class;
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
        $this->indexReturnData(new PengajuanPendanaan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PengajuanPendanaan, 'judul_penelitian');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PengajuanPendanaan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PengajuanPendanaan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PengajuanPendanaan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PengajuanPendanaan, [
            [
                'key' => 'judul_penelitian',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PengajuanPendanaan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PengajuanPendanaan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PengajuanPendanaan);
    }
}
