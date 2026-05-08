<?php

namespace Modules\Core\Extensions\Models\Traits;

use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Error;
use Modules\Core\Helpers\Page;
use Modules\Core\Rules\UniqueCaseInsensitiveRule;

trait ModelValidation
{
    /**
     * Validation rules
     *
     * @var array
     */
    const RULES = [];

    /**
     * Menggunakan booted agar dilakukan setelah semua boot.
     *
     * @return void
     */
    protected static function booted()
    {
        // kurang cocok karena saving dilakukan pertama
        // static::saving(function ($data) { ...

        static::creating(function ($data) {
            static::validate($data);
        });
        static::updating(function ($data) {
            static::validate($data);
        });
    }

    /**
     * Get the validation rules that apply to the model.
     *
     * @param array $data
     * @param array $override
     *
     * @return array
     */
    public static function rules($data = [], $override = []): array
    {
        // override method jika ingin mendefinisikan validation rules langsung
        $rules = static::getRules();

        if (!empty($override)) {
            foreach ($override as $k => $v) {
                $rules[$k] = ($v ?? []) + ($rules[$k] ?? []);
            }
        }

        foreach ($rules as $k => $v) {
            $rules[$k] = static::getValidationRules($k, $v, $data);
        }
        return $rules;
    }

    /**
     * Mengubah field attribute menjadi validation rules.
     *
     * @param string $field
     * @param array $props
     * @param array $data
     *
     * @return array
     */
    protected static function getValidationRules($field, $props, $data = []): array
    {
        $rules = [];

        // required
        $rules[] = empty($props['required']) ? 'nullable' : 'required';

        // type
        if (!empty($props['type'])) {
            $type = $props['type'];

            // menggunakan filter email php
            if ($type == 'email') {
                $type .= ':filter';
                $rules[] = 'lowercase';
            }

            // konversi type
            if ($type == 'timestamp') {
                $type = 'date';
            }

            // integer dan numeric
            if (($type == 'integer' || $type == 'numeric') && !isset($props['min'])) {
                $rules[] = 'min:0';
            }

            $rules[] = $type;
        }

        // maxlength
        if (!empty($props['maxlength'])) {
            if (isset($props['type']) && $props['type'] == 'numeric') {
                $rules[] = 'max_digits:' . $props['maxlength'];
            } else {
                $rules[] = 'max:' . $props['maxlength'];
            }
        }

        // min
        if (isset($props['min'])) {
            $rules[] = 'min:' . $props['min'];
        }

        // max
        if (isset($props['max'])) {
            $rules[] = 'max:' . $props['max'];
        }

        // options
        if (!empty($props['options'])) {
            if (is_array($props['options'])) {
                $rules[] = Rule::in(array_keys($props['options']));
            } else {
                $rules[] = Rule::exists($props['options'], 'id');
            }
        }

        // unique
        if (!empty($props['unique'])) {
            $deletedAtColumn = defined(static::class.'::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
            $rules[] = Rule::unique(static::class, $field)->ignore($data['id'] ?? null)->withoutTrashed($deletedAtColumn);
        }

        // unique case insensitive
        if (!empty($props['unique_ci'])) {
            $deletedAtColumn = defined(static::class . '::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
            $rules[] = new UniqueCaseInsensitiveRule(model: static::class, column: $field, ignoreId: $data['id'] ?? null, deletedAtColumn: $deletedAtColumn);
        }

        // tambahan
        if (!empty($props['validation'])) {
            if (is_string($props['validation'])) {
                $props['validation'] = explode('|', $props['validation']);
            }

            $rules = array_merge($rules, $props['validation']);
        }

        return $rules;
    }

    /**
     * Validasi data.
     *
     * @param mixed $data
     * @throws Exception
     */
    protected static function validate($data)
    {
        // termasuk field hidden
        $hidden = $data->getHidden();
        if (!empty($hidden)) {
            $data->makeVisible($hidden);
        }

        [, $module, , $resource] = explode('\\', static::class);

        $module = strtolower($module);
        $resource = Str::snake($resource);
        $attributes = Page::translateResource($resource, false, $module);

        // validasi dari const RULES model
        Validator::make($data->toArray(), static::rules($data), [], $attributes)->validate();

        // validasi composite unique model
        self::validateUniqueComposite($data);
    }

    /**
     * Field attributes.
     *
     * @param array $fields
     * @return array
     */
    protected static function getRules($fields = []): array
    {
        // cek static rules
        $staticRules = defined('static::RULES') ? static::RULES : [];

        // diurutkan berdasarkan fields
        if (!empty($fields)) {
            $rules = [];
            foreach ($fields as $field) {
                $rules[$field] = $staticRules[$field] ?? [];
            }

            return $rules;
        }

        return $staticRules;
    }

    /**
     * Validate unique composite.
     *
     * @param $data
     * @return void
     * @throws Exception
     */
    private static function validateUniqueComposite($data = [])
    {
        $uniqueColumns = static::getUniqueColumns();
        if (!empty($uniqueColumns)) {
            foreach ($uniqueColumns as $codeUnique => $constraint) {
                $query = static::query();

                // cek apakah nge-use trait softdelete
                if (method_exists(static::class, 'softDeleted')) {
                    $query->whereNull(static::DELETED_AT);
                }

                // looping fields composite unique
                foreach ($constraint['fields'] as $column) {
                    $query->where($column, $data->{$column});
                }

                // cek jika update data
                if ($data?->exists) {
                    $query->where('id', '<>', $data->id);
                }

                // cek hasil query apakah ada yang sama
                if ($query->exists()) {
                    $defaultMessage = $constraint['defaultMessage'] ?? 'Gagal menyimpan karena duplikasi data.';
                    $message = [
                        'defaultMessage' => $defaultMessage,
                        'codeUnique' => $codeUnique,
                    ];

                    throw new Exception(json_encode($message), Error::SQLSTATE_VIOLATION_UNIQUE);
                }
            }
        }
    }

    /**
     * Get unique columns composite.
     * @return array
     */
    protected static function getUniqueColumns(): array
    {
        return method_exists(static::class, 'uniqueColumns') ? static::uniqueColumns() : [];
    }

    /**
     * Utk validasi unique composite.
     * Syarat utk menggunakannya adalah membuat method uniqueColumns() di model.
     * Dan di DB harus ada constraint unique composite.
     *
     * @return array[]
     */
    protected static function uniqueColumns(): array
    {
        return [];
    }
}
