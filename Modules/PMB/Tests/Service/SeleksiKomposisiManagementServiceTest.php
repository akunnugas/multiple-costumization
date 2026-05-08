<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\SeleksiKomposisiManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SeleksiKomposisi;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SeleksiKomposisiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SeleksiKomposisiManagementService
     */
    protected $service = SeleksiKomposisiManagementService::class;
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
        $this->indexReturnData(new SeleksiKomposisi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new SeleksiKomposisi, 'id_periode_pendaftaran', null, '=');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new SeleksiKomposisi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SeleksiKomposisi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SeleksiKomposisi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SeleksiKomposisi, [
            [
                'key' => 'persentase_komposisi',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SeleksiKomposisi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SeleksiKomposisi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SeleksiKomposisi);
    }
}
