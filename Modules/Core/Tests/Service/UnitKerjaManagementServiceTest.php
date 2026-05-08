<?php

namespace Modules\Core\Tests\Service;

use Modules\Gate\Models\User;
use Modules\Core\Models\UnitKerja;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;
use Modules\Core\Services\UnitKerjaManagementService;

class UnitKerjaManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    protected $service = UnitKerjaManagementService::class;
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
        $this->indexReturnData(new UnitKerja);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new UnitKerja, 'nama_unit');
    }

    /**
     * Test to retrieve universities
     */
    public function test_index_universities()
    {
        UnitKerja::factory(2)->create([
            'jenis_unit' => UnitKerja::UNIVERSITY
        ]);

        $data =  $this->service->indexUniversities();

        $this->assertTrue($data->items[0]['jenis_unit'] === UnitKerja::UNIVERSITY);
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to retrieve faculties
     */
    public function test_index_faculties()
    {
        UnitKerja::factory(2)->create([
            'jenis_unit' => UnitKerja::FACULTY
        ]);

        $data =  $this->service->indexFaculties();

        $this->assertTrue($data->items[0]['jenis_unit'] === UnitKerja::FACULTY);
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to retrieve study programs
     */
    public function test_index_study_programs()
    {
        UnitKerja::factory(2)->create([
            'jenis_unit' => UnitKerja::STUDY_PROGRAM
        ]);

        $data =  $this->service->indexStudyPrograms();

        $this->assertTrue($data->items[0]['jenis_unit'] === UnitKerja::STUDY_PROGRAM);
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new UnitKerja);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new UnitKerja);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new UnitKerja);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new UnitKerja, [
            [
                'key' => 'nama_unit',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new UnitKerja);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new UnitKerja);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new UnitKerja);
    }
}
