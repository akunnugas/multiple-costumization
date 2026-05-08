<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\AgendaKegiatanService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\AgendaKegiatan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class AgendaKegiatanServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var AgendaKegiatanService
     */
    protected $service = AgendaKegiatanService::class;
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
        $this->indexReturnData(new AgendaKegiatan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new AgendaKegiatan, 'name');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new AgendaKegiatan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new AgendaKegiatan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new AgendaKegiatan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new AgendaKegiatan, [
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
        $this->updateDataThrowModelNotFound(new AgendaKegiatan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new AgendaKegiatan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new AgendaKegiatan);
    }
}
