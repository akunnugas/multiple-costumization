<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Core\Helpers\Page;

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
            $rules[] = Rule::unique(static::class, $field)->ignore($data['id'] ?? null)->withoutTrashed();
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

        Validator::make($data->toArray(), static::rules($data), [], $attributes)->validate();
    }

    /**
     * Field attributes.
     *
     * @param array $fields
     *
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
}
