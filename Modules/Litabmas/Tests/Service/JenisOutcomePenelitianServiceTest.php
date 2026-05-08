<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\JenisOutcomePenelitianService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\JenisOutcomePenelitian;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenisOutcomePenelitianServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var JenisOutcomePenelitianService
     */
    protected $service = JenisOutcomePenelitianService::class;
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
        $this->indexReturnData(new JenisOutcomePenelitian);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenisOutcomePenelitian, 'nama_outcome');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenisOutcomePenelitian);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenisOutcomePenelitian);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenisOutcomePenelitian);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenisOutcomePenelitian, [
            [
                'key' => 'nama_outcome',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenisOutcomePenelitian);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenisOutcomePenelitian);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenisOutcomePenelitian);
    }
}
