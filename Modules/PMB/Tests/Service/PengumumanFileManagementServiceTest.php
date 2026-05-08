<?php

namespace Modules\PMB\Tests\Service;

use Modules\PMB\Services\PengumumanFileManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\PengumumanFile;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class PengumumanFileManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var PengumumanFileManagementService
     */
    protected $service = PengumumanFileManagementService::class;
    protected $auth;

    protected function prepare(): void
    {
        // Setup auth
        $user = User::factory()->create();
        $this->auth = $this->actingAs($user);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new PengumumanFile);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new PengumumanFile, [
            [
                'key' => 'id_pengumuman',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new PengumumanFile);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new PengumumanFile);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new PengumumanFile);
    }
}
