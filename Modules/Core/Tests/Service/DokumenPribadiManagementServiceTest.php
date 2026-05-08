<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\DokumenPribadiManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\DokumenPribadi;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class DokumenPribadiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var DokumenPribadiManagementService
     */
    protected $service = DokumenPribadiManagementService::class;
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
        $this->indexReturnData(new DokumenPribadi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new DokumenPribadi, 'status_dokumen', DokumenPribadi::STATUS_VALID);
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new DokumenPribadi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new DokumenPribadi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new DokumenPribadi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new DokumenPribadi, [
            [
                'key' => 'status_dokumen',
                'val' => DokumenPribadi::STATUS_ON_REVIEW
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new DokumenPribadi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new DokumenPribadi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new DokumenPribadi);
    }
}
