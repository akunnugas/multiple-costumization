<?php

namespace Modules\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniqueCaseInsensitiveRule implements ValidationRule
{
    protected string $module;
    protected string $table;
    protected string $column;
    protected ?int $ignoreId;
    protected ?string $deletedAtColumn;

    /**
     * Create a new rule instance.
     *
     * @param string $model
     * @param string $column
     * @param int|null $ignoreId
     * @param string|null $deletedAtColumn
     */
    public function __construct(string $model, string $column, int $ignoreId = null, string $deletedAtColumn = null)
    {
        $this->table = app($model)->getTable();

        [, $module, , $model] = explode('\\', $model);
        $this->module = Str::lower($module);
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->deletedAtColumn = $deletedAtColumn;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)
            ->whereRaw("LOWER({$this->column}) = ?", [strtolower($value)]);

        if ($this->ignoreId) {
            $query->where('id', '<>', $this->ignoreId);
        }

        if ($this->deletedAtColumn) {
            $query->whereNull($this->deletedAtColumn);
        }

        if ($query->count() != 0) {
            $fail($this->message());
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $table = explode('.', $this->table)[1];
        return __('validation.unique', [
            'attribute' => __($this->module . '::' . $table . '.' . $this->column),
        ]);
    }
}
