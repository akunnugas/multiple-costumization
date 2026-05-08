<?php

namespace Modules\Core\Tests\Service;

use Illuminate\Support\Arr;
use Modules\Core\Helpers\Error;
use Modules\Core\Services\JenisDokumenManagementService;
use Modules\Gate\Models\User;
use Modules\Core\Models\JenisDokumen;
use Tests\ServiceTestCase;
use Tests\Traits\ManagementServiceTest;

class JenisDokumenManagementServiceTest extends ServiceTestCase
{
    use ManagementServiceTest;

    /**
     * @var JenisDokumenManagementService
     */
    protected $service = JenisDokumenManagementService::class;
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
        $this->indexReturnData(new JenisDokumen);
    }

    /**
     * Test index to retrieve data with filter
     */
    public function test_index_return_data_with_filter()
    {
        $this->indexReturnDataWithFilter(new JenisDokumen, 'nama_jenis_dokumen');
    }

    /**
     * Test to show data
     */
    public function test_show()
    {
        $this->showData(new JenisDokumen);
    }

    /**
     * Test to show data with throw model not found
     */
    public function test_show_throw_model_not_found()
    {
        $this->showDataThrowModelNotFound(new JenisDokumen);
    }

    /**
     * Test to store data
     */
    public function test_store_data()
    {
        $this->storeData(new JenisDokumen);
    }

    /**
     * Test to update data
     */
    public function test_update_data()
    {
        $this->updateData(new JenisDokumen, [
            [
                'key' => 'nama_jenis_dokumen',
                'val' => fake()->name()
            ]
        ]);
    }

    /**
     * Test to update data with throw is static can not be updated
     *
     * @return void
     */
    public function test_update_data_throw_is_static_can_not_be_updated()
    {
        $fake = JenisDokumen::factory()->make(['apakah_statis' => true])->toArray();
        $data = JenisDokumen::factory($fake)->create();
        $created = Arr::except($data->toArray(), ['id']);

        // update name
        $created['nama_jenis_dokumen'] = fake()->name();
        $model = $this->service->update($created, $data->id);

        // assert return error
        $this->assertInstanceOf(Error::class, $model);
        // assert data tidak berubah
        $this->assertDatabaseHas($data->getTable(), Arr::except($data->toArray(), ['waktu_dibuat', 'waktu_diubah']));
    }

    /**
     * Test to update data with throw model not found
     */
    public function test_update_data_throw_model_not_found()
    {
        $this->updateDataThrowModelNotFound(new JenisDokumen);
    }

    /**
     * Test to destroy data
     */
    public function test_destroy_data()
    {
        $this->destroyData(new JenisDokumen);
    }

    public function test_destroy_data_throw_is_static_can_not_be_updated()
    {
        $fake = JenisDokumen::factory()->make(['apakah_statis' => true])->toArray();
        $data = JenisDokumen::factory($fake)->create();

        // hapus data
        $model = $this->service->destroy($data->id);

        // assert return error
        $this->assertInstanceOf(Error::class, $model);
        // assert data tidak berubah
        $this->assertDatabaseHas($data->getTable(), Arr::except($data->toArray(), ['waktu_dibuat', 'waktu_diubah']));
    }

    /**
     * Test to destroy data with throw model not found
     */
    public function test_destroy_data_throw_model_not_found()
    {
        $this->destroyDataThrowModelNotFound(new JenisDokumen);
    }
}
