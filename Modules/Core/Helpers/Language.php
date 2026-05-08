<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Lang;

class Language
{
    const ACTION_CREATE = 'create';
    const ACTION_DELETE = 'delete';
    const ACTION_SAVE = 'save';
    const ACTION_UPDATE = 'update';
    const ACTION_SUBMIT = 'submit';
    const ACTION_CANCEL_SUBMIT = 'cancel_submit';

    const STATUS_FAILED = 'failed';
    const STATUS_SUCCESS = 'success';

    /**
     * @param string $module
     * @param string $resource
     * @param string $action
     * @param string $status
     * @return string
     */
    public static function alertTitle(string $module, string $resource, string $action, string $status)
    {
        // resource
        $message = Language::resourceName($module, $resource, true);

        return static::alertWithoutResource($action, $status, $message);
    }

    /**
     * @param string $action
     * @param string $status
     * @param string|null $message
     * @return string
     */
    public static function alertWithoutResource(string $action, string $status, string $message = 'data')
    {
        // action
        $key = 'message.' . $action;
        if (Lang::has($key)) {
            $message = __($key, ['attribute' => $message]);
        } else {
            $message = $action;
        }

        // status
        $key = 'message.' . $status;
        if (Lang::has($key)) {
            $message = __($key, ['attribute' => $message]);
        } else {
            $message .= ' ' . $status;
        }

        return $message . '.';
    }

    /**
     * Buat resource title.
     */
    public static function resourceName($module, $resource, $lower = false)
    {
        $resource = str_replace('-', '_', $resource);

        $key = $module . '::' . $resource . '.lower';
        if ($lower && Lang::has($key)) {
            return __($key);
        }

        $key = $module . '::' . $resource . '.main';
        if (Lang::has($key)) {
            $name = __($key);
        }

        if (empty($name)) {
            return null;
        }

        if ($lower) {
            return strtolower($name);
        }

        return $name;
    }
}
