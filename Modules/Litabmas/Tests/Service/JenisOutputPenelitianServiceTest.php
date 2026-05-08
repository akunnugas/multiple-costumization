<?php

namespace Modules\Litabmas\Tests\Service;

use Modules\Litabmas\Services\JenisOutputPenelitianService;
use Modules\Gate\Models\User;
use Modules\Litabmas\Models\JenisOutputPenelitian;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenisOutputPenelitianServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var JenisOutputPenelitianService
     */
    protected $service = JenisOutputPenelitianService::class;
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
        $this->indexReturnData(new JenisOutputPenelitian);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenisOutputPenelitian, 'nama_output');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenisOutputPenelitian);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenisOutputPenelitian);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenisOutputPenelitian);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenisOutputPenelitian, [
            [
                'key' => 'nama_output',
                'val' => fake()->sentence(3),
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenisOutputPenelitian);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenisOutputPenelitian);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenisOutputPenelitian);
    }
}
