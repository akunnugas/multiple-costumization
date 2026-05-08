<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Modules\Core\Extensions\SevimaSchema;
use Modules\Core\Helpers\Pagination;

trait ManagementServiceTest
{
    public function indexReturnData(Model $model)
    {
        $model::factory(3)->create();

        $data = $this->service->index();

        $this->assertInstanceOf(Pagination::class, $data);
        $this->assertNotEmpty($data->items[0]['id']);
    }

    public function indexReturnDataWithFilter(Model $model, string $key, string|null $val = null, string|null $operator = 'ilike')
    {
        $model::factory(3)->create();

        $data = $this->service->index(filter: [
            ['field' => $key, 'value' => $val ?? $model->first()->{$key}, 'operator' => $operator]
        ]);

        $this->assertNotEmpty($data->items);
    }

    public function showData(Model $model)
    {
        $created = $model::factory()->create();
        $model = $this->service->show($created->id);

        $this->assertEquals($created['id'], $model['id']);
    }

    public function showDataThrowModelNotFound(Model $model)
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->show(fake()->randomNumber(6));
    }

    public function storeData(Model $model)
    {
        $data = $model::factory()->make()->toArray();
        $stored = $this->service->store($data);

        $this->assertDatabaseHas($stored->getTable(), Arr::except($stored->toArray(), ['created_at', 'updated_at']));
    }

    public function updateData(Model $model, array $columns = [])
    {
        $fake = $model::factory()->make()->toArray();
        $data = $model::factory($fake)->create();
        $created = Arr::except($data->toArray(), ['id']);

        // update
        if (!empty($columns)) {
            foreach ($columns as $column) {
                $created[$column['key']] = $column['val'];
            }
        }

        $updated = $this->service->update($created, $data->id);

        $this->assertDatabaseHas($updated->getTable(), Arr::except($updated->toArray(), ['created_at', 'updated_at']));
    }

    public function updateDataThrowModelNotFound(Model $model)
    {
        $this->expectException(ModelNotFoundException::class);

        $data = $model::factory()->make()->toArray();

        $this->service->update($data, fake()->randomNumber());
    }

    public function destroyData(Model $model)
    {
        $data = $model::factory()->create();
        $isSoftDelete = SevimaSchema::hasColumn($data->getTable(), 'waktu_dihapus');
        $isUseSoftdeleteTrait = in_array(SoftDeletes::class, class_uses($model::class), true);
        $assertMethod = $isSoftDelete && $isUseSoftdeleteTrait ? 'assertSoftDeleted' : 'assertDatabaseMissing';

        $this->service->destroy($data->id);

        $this->{$assertMethod}($data->getTable(), [
            'id' => $data->id
        ]);
    }

    public function destroyDataThrowModelNotFound(Model $model)
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->destroy(fake()->randomNumber());
    }
}
