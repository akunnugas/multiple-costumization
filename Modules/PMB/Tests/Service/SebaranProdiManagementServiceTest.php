<?php

namespace Modules\PMB\Tests\Service;

use Modules\Core\Helpers\Pagination;
use Modules\PMB\Models\SebaranAsalPendaftar;
use Modules\PMB\Services\SebaranProdiManagementService;
use Modules\Gate\Models\User;
use Modules\PMB\Models\SebaranProdi;
use Modules\PMB\Models\PeriodePendaftaran;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class SebaranProdiManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var SebaranProdiManagementService $service
     */
    protected $service = SebaranProdiManagementService::class;
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
        $registrationPeriod = PeriodePendaftaran::factory()->create();

        SebaranProdi::factory(3)->create([
            'id_periode_pendaftaran' => $registrationPeriod->id
        ]);

        $this->service->setRegistrationPeriodId($registrationPeriod->id);
        $data = $this->service->index();

        // assert
        $this->assertInstanceOf(Pagination::class, $data);
        $this->assertNotEmpty($data->items[0]['id']);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $registrationPeriod = PeriodePendaftaran::factory()->create();

        SebaranProdi::factory(3)->create([
            'id_periode_pendaftaran' => $registrationPeriod->id
        ]);

        $this->service->setRegistrationPeriodId($registrationPeriod->id);

        $data = $this->service->index(filter: [[
            'field' => 'id_periode_pendaftaran',
            'value' => (new SebaranProdi)->first()->id_periode_pendaftaran,
            'operator' => '='
        ]]);

        // assert with filter
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $created = SebaranProdi::factory()->create();
        $model = $this->service->show($created->id);

        // assert
        $this->assertEquals($created['id'], $model['id']);
    }

    public function test_show_has_attribute_to_program_institution_mapping()
    {
        // program institution mapping
        $programInstitutionMappings = SebaranAsalPendaftar::factory()->create();
        $programDistributionId = $programInstitutionMappings->first()->id_sebaran_prodi;
        $institutionTypeId = $programInstitutionMappings->first()->id_jenis_institusi;

        // program distribution
        $programDistribution = $this->service->show($programDistributionId);

        // assert has program_institution_{program_institution_type_id}
        $this->assertArrayHasKey('program_institution_' . $institutionTypeId, $programDistribution);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new SebaranProdi);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new SebaranProdi);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new SebaranProdi, [
            [
                'key' => 'nim_prefix',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new SebaranProdi);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new SebaranProdi);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new SebaranProdi);
    }
}
