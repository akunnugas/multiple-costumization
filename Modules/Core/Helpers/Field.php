<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Nwidart\Modules\Facades\Module;

class Field
{
    /**
     * Melengkapi item field
     */
    public static function getFieldItems($items, $record = null, $nav = null, $withRules = true, $prefix = null)
    {
        // navigation helper
        if (empty($nav)) {
            $nav = Navigation::getInstance();
        }

        $rules = [];
        foreach ($items as $i => $item) {
            $field = $item['field'];

            // refer
            $refer = $item['refer'] ?? null;
            if (!empty($refer)) {
                $part = explode('\\', $refer);

                $module = Module::find($part[1])->getLowerName();
                $resource = Str::kebab($part[count($part) - 1]);
            } else {
                $module = $item['module'] ?? $nav->module;
                $resource = $item['resource'] ?? $nav->resource;
            }

            if ($withRules) {
                $rules[$module][$resource] ??= static::getRules($module, $resource);
                $rule = $rules[$module][$resource][$field] ?? null;
            }

            // label
            $item['label'] ??= static::getFieldLabel($field, $module, $resource, $prefix);

            // dynamic component
            if (!empty($item['component'])) {
                $item['component'] = Field::getDynamicValue($item['component'], $module, $field);
            }

            // value
            if (!empty($record)) {
                if (!empty($prefix)) {
                    $item['value'] = $record[$prefix][$field] ?? null;
                } else {
                    $item['value'] = $record[$field] ?? null;
                }

                $item = static::buildAutocomplete($item);
            }

            // rules
            if (!empty($rule)) {
                $item += $rule;
            }

            $items[$i] = $item;
        }

        return $items;
    }

    /**
     * Melengkapi label field
     */
    public static function getFieldLabel($field, $module, $resource, $prefix = null)
    {
        $resource = str_replace('-', '_', $resource);

        if (!empty($prefix)) {
            return __($module . '::' . $resource . '.' . $prefix . '.' . $field);
        }

        return __($module . '::' . $resource . '.' . $field);
    }

    /**
     * Mendapatkan value berdasarkan item
     */
    public static function getItemValue($item, $record)
    {
        $value = $record[$item['field_value'] ?? $item['field']] ?? null;

        // get value by options
        $options = $item['options'] ?? null;
        if (!empty($options)) {
            $value = is_array($options)
                ? ($options[$value] ?? $value)
                : ($options::options()[$value] ?? $value);
        }

        return $value;
    }

    /**
     * Set item value.
     * Dipakai ketika edit inline record/data.
     *
     * @param array $headers
     * @param array $record
     * @return array
     */
    public static function setItemValue(array $headers, array $record)
    {
        $headersMap = array_column($headers, null, 'field');

        foreach ($record as $field => $value) {
            if (isset($value) && isset($headersMap[$field])) {
                $record[$field] = static::formatValueToDatabase($headersMap[$field], $value);
            }
        }

        return $record;
    }

    /**
     * Format value for database format.
     *
     * @param array $item
     * @param mixed $value
     * @return mixed|string|void
     */
    public static function formatValueToDatabase(array $item, mixed $value)
    {
        // validasi harus memiliki satu antara type atau control
        $control = $item['control'] ?? null;
        $type = $item['type'] ?? null;
        if (empty($type) && empty($control)) {
            return is_string($value) ? trim($value) : $value;
        }

        // handle null
        if (empty($value)) {
            return null;
        }

        // handle control
        if (isset($control)) {
            // handle value file yg bukan temporary nya livewire akan diabaikan
            if ($control === 'simple-file' && !($value instanceof TemporaryUploadedFile)) {
                return null;
            }

            return $value;
        }

        // handle type
        if ($type === 'timestamp') {
            $timestampValue = Carbon::parse($value)->format('Y-m-d H:i:sP');
            $timestampValue = preg_replace('/:(\d{2})$/', '', $timestampValue);
        }

        return match ($type) {
            'timestamp' => $timestampValue ?? $value,
            'date' => Carbon::parse($value)->format('Y-m-d'),
            'time' => Carbon::parse($value)->format('H:i:s'),
            default => $value,
        };
    }

    /**
     * Formating value to display (human-readable)
     *
     * @param array $item
     * @param mixed $value
     * @return mixed|string|void
     */
    public static function formatValueToDisplay(array $item, mixed $value)
    {
        // handle tidak ada type atau null
        // todo: pengecekan type bisa dihapus jika sudah butuh formating selain type
        if (!isset($item['type']) || empty($value)) {
            return $value;
        }

        $type = $item['type'];
        return match ($type) {
            'timestamp' => Carbon::parse($value)->format('Y-m-d H:i'),
            'date' => Carbon::parse($value)->format('Y-m-d'),
            'time' => Carbon::parse($value)->format('H:i'),
            default => $value,
        };
    }

    /**
     * Melengkapi item untuk autocomplete
     */
    public static function buildAutocomplete($item)
    {
        if (empty($item['control']) || $item['control'] != 'autocomplete') {
            return $item;
        }

        if (empty($item['field_label'])) {
            $item['field_label'] = $item['field'] . '_label';
        }

        $item['value_label'] = $record[$item['field_label']] ?? null;
        if (!empty($item['value_label']) || empty($item['value'])) {
            return $item;
        }

        $model = $item['model'] ?? null;
        if (empty($model)) {
            return $item;
        }

        $item['value_label'] = $model::optionValue($item['value']);

        return $item;
    }

    /**
     * Dynamic component untuk options
     */
    public static function getDynamicOptions($component, $module, $name)
    {
        return static::getDynamicComponent($component, $module, $name, 'options');
    }

    /**
     * Dynamic component untuk value
     */
    public static function getDynamicValue($component, $module, $name)
    {
        return static::getDynamicComponent($component, $module, $name, 'fields');
    }

    /**
     * Rules
     */
    private static function getRules($module, $resource)
    {
        $module = Module::find($module);
        if ($module) {
            $model = '\Modules\\' . $module->getStudlyName() . '\Models\\' . Str::studly($resource);
        }
        if (!empty($model) && class_exists($model)) {
            return $model::RULES;
        }

        return [];
    }

    /**
     * Dynamic component
     */
    private static function getDynamicComponent($component, $module, $name, $prefix)
    {
        // contoh: gate::options.role
        if (strpos($component, '::') !== false) {
            return $component;
        }

        // contoh: gate.role
        if ($component !== true) {
            // explode 0 dianggap sebagai module, 1 dan sisanya dianggap sebagai name
            $component = explode('.', $component);
            $module = $component[0];
            $name = implode('.', array_slice($component, 1));
        }

        return $module . '::' . $prefix . '.' . $name;
    }
}
