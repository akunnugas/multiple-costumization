<?php

namespace Modules\HR\Tests\Service;

use Modules\HR\Services\JabatanAkademikManagementService;
use Modules\Gate\Models\User;
use Modules\HR\Models\JabatanAkademik;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JabatanAkademikManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = JabatanAkademikManagementService::class;
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
        $this->indexReturnData(new JabatanAkademik);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JabatanAkademik, 'nama_jabatan_akademik');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JabatanAkademik);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JabatanAkademik);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JabatanAkademik);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JabatanAkademik, [
            [
                'key' => 'nama_jabatan_akademik',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JabatanAkademik);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JabatanAkademik);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JabatanAkademik);
    }
}
