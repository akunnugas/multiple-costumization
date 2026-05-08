<?php

namespace Modules\Core\Tests\Service;

use Modules\Gate\Models\User;
use Modules\Core\Models\Wilayah;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;
use Modules\Core\Services\WilayahManagementService;

class WilayahManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var WilayahManagementService $service
     */
    protected $service = WilayahManagementService::class;
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
        $this->indexReturnData(new Wilayah);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new Wilayah, 'nama_wilayah');
    }

    /**
     * Test to retrieve countries
     */
    public function test_index_countries()
    {
        Wilayah::factory(5)->create([
            'level_wilayah' => Wilayah::LEVEL_COUNTRY
        ]);

        $data =  $this->service->indexCountries();

        $this->assertTrue($data->items[0]['level_wilayah'] == Wilayah::LEVEL_COUNTRY);
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to retrieve provinces
     */
    public function test_index_provinces()
    {
        Wilayah::factory(5)->create([
            'level_wilayah' => Wilayah::LEVEL_PROVINCE
        ]);

        $data =  $this->service->indexProvinces();

        $this->assertTrue($data->items[0]['level_wilayah'] == Wilayah::LEVEL_PROVINCE);
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to retrieve study cities
     */
    public function test_index_cities()
    {
        Wilayah::factory(5)->create([
            'level_wilayah' => Wilayah::LEVEL_CITY
        ]);

        $data =  $this->service->indexCities();

        $this->assertTrue($data->items[0]['level_wilayah'] == Wilayah::LEVEL_CITY);
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to retrieve study districts
     */
    public function test_index_districts()
    {
        Wilayah::factory(5)->create([
            'level_wilayah' => Wilayah::LEVEL_DISTRICT
        ]);

        $data =  $this->service->indexDistricts();

        $this->assertTrue($data->items[0]['level_wilayah'] == Wilayah::LEVEL_DISTRICT);
        $this->assertNotEmpty($data->items);
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new Wilayah);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new Wilayah);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new Wilayah);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new Wilayah, [
            [
                'key' => 'nama_wilayah',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new Wilayah);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new Wilayah);
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new Wilayah);
    }
}
