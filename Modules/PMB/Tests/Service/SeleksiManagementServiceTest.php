<?php

namespace Modules\PMB\Tests\Service;

use Modules\Core\Helpers\Pagination;
use Modules\Gate\Models\User;
use Modules\PMB\Models\Seleksi;
use Modules\PMB\Services\SeleksiManagementService;
use Modules\PMB\Services\PeriodePendaftaranManagementService;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SeleksiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = SeleksiManagementService::class;
    protected $registrationPeriodService = PeriodePendaftaranManagementService::class;
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
        // Create data
        $programAssessment = Seleksi::factory()->create();
        $registrationPeriodId = $programAssessment->programDistribution->registrationPeriod->id;

        // Set parent resource id
        $this->service->setParentResourceId($registrationPeriodId);

        // Get data
        $data = $this->service->index();

        // Assertion
        $firstData = $data->items[0];
        $this->assertInstanceOf(Pagination::class, $data);
        $this->assertNotEmpty($firstData['id']);
        $this->assertEquals($registrationPeriodId, $firstData['id_periode_pendaftaran']);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        // Create data
        $programAssessment = Seleksi::factory()->create();
        $registrationPeriodId = $programAssessment->programDistribution->registrationPeriod->id;

        // Set parent resource id
        $this->service->setParentResourceId($registrationPeriodId);

        // Get data
        $data = $this->service->index(filter: [
            ['field' => 'id_sebaran_prodi', 'value' => $programAssessment->id_sebaran_prodi],
        ]);

        // Assertion
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Seleksi);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Seleksi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Seleksi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Seleksi, [
            [
                'key' => 'urutan_seleksi',
                'val' => fake()->randomDigit(),
            ],
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Seleksi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Seleksi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Seleksi);
    }
}
