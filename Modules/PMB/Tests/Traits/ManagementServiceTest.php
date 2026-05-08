<?php

namespace Modules\PMB\Tests\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;

trait ManagementServiceTest
{
    public function indexReturnCollection()
    {
        $data = $this->service->index();

        $this->assertInstanceOf(Collection::class, $data);
    }

    public function indexReturnPaginator()
    {
        $data = $this->service->index(limit: 10);

        $this->assertInstanceOf(AbstractPaginator::class, $data);
    }

    public function showData(Model $model)
    {
        $created = $model::factory()->create();
        $model = $this->service->show($created->id);

        $this->assertEquals($created->id, $model->id);
    }

    public function showDataThrowModelNotFound(Model $model)
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->show(fake()->randomNumber());
    }

    public function storeData(Model $model)
    {
        $data = $model::factory()->make()->toArray();
        $stored = $this->service->store($data);

        $this->assertDatabaseHas($stored->getTable(), $data);
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

        $this->service->destroy($data->id);

        $this->assertDatabaseMissing($data->getTable(), [
            'id' => $data->id
        ]);
    }

    public function destroyDataThrowModelNotFound(Model $model)
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->destroy(fake()->randomNumber());
    }
}
