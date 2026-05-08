<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Arr;
use Modules\Core\Exceptions\ServiceException;
use Throwable;

class ServiceReturn
{
    /**
     * Mengembalikan array data.
     */
    public static function value(...$data)
    {
        return [
            'data' => $data
        ];
    }

    /**
     * Dapatkan error.
     */
    public static function getValue($value)
    {
        $data = $value['data'] ?? null;
        if (empty($data)) {
            return $value;
        }

        // kembalikan value jika hanya memiliki satu elemen
        if (count($data) == 1) {
            return $data[0];
        }

        return $data;
    }

    /**
     * Mengembalikan array error.
     */
    public static function error(string $message = null, string $code = null, string $title = null, int $id = null, Throwable $exception = null)
    {
        $errorReturn = new ServiceError(
            message: $message,
            code: $code,
            title: $title,
            id: $id,
            exception: $exception
        );

        return [
            'error' => (array) $errorReturn,
        ];
    }

    /**
     * Mengembalikan array error dari input enum.
     */
    public static function errorEnum($enum)
    {
        return static::error(message: $enum->message(), code: $enum->code(), id: $enum->id());
    }

    /**
     * Throw ServiceException.
     */
    public static function throwError(array $value)
    {
        $error = self::getError($value);
        $error = Arr::only($error, ['message', 'code', 'title', 'id']);

        throw new ServiceException(...$error);
    }

    /**
     * Replace array error untuk alert.
     */
    public static function errorReplaceAlert(array $value, string $module, string $resource, string $action)
    {
        $error = self::getError($value);

        $title = Language::alertTitle($module, $resource, $action, Language::STATUS_FAILED);
        if ($error['message'] == ServiceError::MESSAGE_DEFAULT) {
            $error['title'] = null;
            $error['message'] = $title;
        } else {
            $error['title'] = $title;
        }

        return [
            'error' => $error
        ];
    }

    /**
     * Replace array error.
     */
    public static function errorReplace(array $value, string $message = null, string $code = null, string $title = null, int $id = null, Throwable $exception = null)
    {
        $error = self::getError($value);

        return [
            'error' => [
                'id' => $id ?? $error['id'],
                'message' => $message ?? $error['message'],
                'code' => $code ?? $error['code'],
                'title' => $title ?? $error['title'],
                'exception' => $exception ?? $error['exception'],
            ]
        ];
    }

    /**
     * Cek apakah error.
     */
    public static function isError($value)
    {
        return is_array($value) && !empty($value['error']);
    }

    /**
     * Dapatkan error.
     */
    public static function getError($value, $index = null)
    {
        $error = $value['error'] ?? null;

        if (empty($error) || empty($index)) {
            return $error;
        }

        return $error[$index] ?? null;
    }
}