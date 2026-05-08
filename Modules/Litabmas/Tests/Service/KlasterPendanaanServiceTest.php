<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\KlasterPendanaanService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\KlasterPendanaan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class KlasterPendanaanServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var KlasterPendanaanService
     */
    protected $service = KlasterPendanaanService::class;
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
        $this->indexReturnData(new KlasterPendanaan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new KlasterPendanaan, 'fc.nama_klaster');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new KlasterPendanaan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new KlasterPendanaan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new KlasterPendanaan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new KlasterPendanaan, [
            [
                'key' => 'nama_klaster',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new KlasterPendanaan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new KlasterPendanaan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new KlasterPendanaan);
    }
}
