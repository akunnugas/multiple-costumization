<?php

namespace Modules\SPMI\Tests\Service;

use Modules\SPMI\Services\PenilaianMatriksPredikatManagementService;
use Modules\Gate\Models\User;
use Modules\SPMI\Models\PenilaianMatriksPredikat;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PenilaianMatriksPredikatManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PenilaianMatriksPredikatManagementService
     */
    protected $service = PenilaianMatriksPredikatManagementService::class;
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
        $this->indexReturnData(new PenilaianMatriksPredikat);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new PenilaianMatriksPredikat, 'deskripsi');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new PenilaianMatriksPredikat);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new PenilaianMatriksPredikat);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PenilaianMatriksPredikat);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PenilaianMatriksPredikat, [
            [
                'key' => 'deskripsi',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PenilaianMatriksPredikat);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PenilaianMatriksPredikat);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PenilaianMatriksPredikat);
    }
}
