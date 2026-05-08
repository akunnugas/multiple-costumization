<?php

namespace Modules\Core\Helpers;

class MenuHelper
{
    public static function getItem(string $menuClass, string $key = '', $includes = false)
    {
        if (empty($key) || empty($menuClass)) {
            return [];
        }

        $explodedKey = explode('.', $key);
        $methodName = $explodedKey[0] ?? '';
        $keyMethodName = $explodedKey[1] ?? '';
        $searchedKey = end($explodedKey) ?? '';

        $values = (new $menuClass)->$methodName($keyMethodName);

        if (!isset($values)) {
            return [];
        }

        $result = [];
        static::flattenItems($values, $result, $values['parent']);

        if ($includes) {
            if (!empty($values['parent_sidebar'])) {
                $parentValues = static::getItem($menuClass, $values['parent_sidebar']);
            }

            $children = array_values(array_filter($result, function ($item) use ($searchedKey) {
                return str_contains($item['path'], $searchedKey);
            }))[0] ?? [];

            $parent = array_values(array_filter($result, function ($item) use ($children) {
                return str_contains($item['path'], $children['parent_path']);
            }))[0] ?? [];

            return [
                ...($parentValues ?? []),
                'items' => [[
                    ...$parent,
                    'items' => [
                        $children
                    ]
                ]]
            ];
        } else {
            $activePath = collect($result)->firstWhere('path', $searchedKey);
            $parent = array_values(array_filter($result, function ($item) use ($activePath) {
                return str_contains($item['path'], $activePath['parent_path']);
            }))[0] ?? [];

            return [
                ...$parent,
                'items' => [
                    $activePath
                ]
            ];
        }
    }

    public static function flattenItems($array, &$result = [], $parentPath = null)
    {
        foreach ($array as $key => $value) {
            if (!empty($value['parent'])) {
                continue;
            }

            if (is_array($value)) {
                if (!empty($parentPath)) {
                    $value['parent_path'] = $parentPath;
                }

                if ($key === 'items') {
                    self::flattenItems($value, $result, $value['path'] ?? $parentPath);
                    continue;
                }

                if (!empty($value['items'])) {
                    if (isset($value['path'])) {
                        $result[] = $value;
                    }

                    self::flattenItems($value['items'], $result, $value['path'] ?? $parentPath);
                    continue;
                }

                $result[] = $value;
                continue;
            }

            if (!empty($value['items'])) {
                self::flattenItems($value['items'], $result, $value['path'] ?? $parentPath);
                continue;
            }
        }

        return $result;
    }
}
