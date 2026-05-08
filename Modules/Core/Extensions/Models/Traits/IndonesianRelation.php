<?php

namespace Modules\Core\Extensions\Models\Traits;

use Illuminate\Support\Str;

trait IndonesianRelation
{
    /**
     * The schema associated with the model.
     *
     * @var string
     */
    protected $schema;

    /**
     * Get the schema associated with the model.
     *
     * @return string
     */
    public function getSchema()
    {
        [, $module] = explode('\\', static::class);

        return $this->schema ?? Str::snake(Str::studly($module));
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? $this->getSchema() . '.' . Str::snake(Str::studly(class_basename($this)));
    }

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->getKeyName() . '_' . Str::snake(class_basename($this));
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $ownerKey
     * @param  string|null  $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        // asumsi pk sama
        if (is_null($foreignKey)) {
            $foreignKey = $this->getKeyName() . '_' . Str::snake($relation ?? class_basename($related));
        }

        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }
}
