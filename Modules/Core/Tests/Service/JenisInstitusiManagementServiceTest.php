<?php

namespace Modules\Core\Tests\Service;

use Modules\Core\Services\JenisInstitusiManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\JenisInstitusi;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenisInstitusiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var JenisInstitusiManagementService
     */
    protected $service = JenisInstitusiManagementService::class;
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
        $this->indexReturnData(new JenisInstitusi);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenisInstitusi, 'nama_jenis_institusi');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenisInstitusi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenisInstitusi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenisInstitusi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenisInstitusi, [
            [
                'key' => 'nama_jenis_institusi',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenisInstitusi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenisInstitusi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenisInstitusi);
    }
}
