<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\TemaKegiatanService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\TemaKegiatan;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class TemaKegiatanServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var TemaKegiatanService
     */
    protected $service = TemaKegiatanService::class;
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
        $this->indexReturnData(new TemaKegiatan);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new TemaKegiatan, 'nama_tema');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new TemaKegiatan);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new TemaKegiatan);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new TemaKegiatan);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new TemaKegiatan, [
            [
                'key' => 'nama_tema',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new TemaKegiatan);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new TemaKegiatan);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new TemaKegiatan);
    }
}
