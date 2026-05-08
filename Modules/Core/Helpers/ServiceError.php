<?php

namespace Modules\Core\Helpers;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use Modules\Core\Enums\CoreErrorEnum;
use Modules\Core\Exceptions\ServiceException;
use Throwable;

class ServiceError
{
    const ERROR_FORBIDDEN = 403;
    const ERROR_NOT_FOUND = 404;
    const ERROR_CONFLICT = 409;
    const ERROR_VALIDATION = 422;
    const ERROR_DEFAULT = 590;

    const MESSAGE_DEFAULT = 'Terjadi kesalahan';

    const SQLSTATE_VIOLATION_FK = 23503; // error CODE SQLSTATE foreign key violation
    const SQLSTATE_VIOLATION_UNIQUE = 23505; // error CODE SQLSTATE unique violation

    /**
     * @throws Throwable
     */
    public function __construct(
        public ?string    $message = null,
        public ?string    $code = null,
        public ?string    $title = null,
        public ?int       $id = null,
        public ?Throwable $exception = null
    ) {
        // attribute yg harus ada isinya (dikasih default)
        $this->id ??= self::ERROR_DEFAULT;
        $this->message ??= self::MESSAGE_DEFAULT;

        $this->checkException();
    }

    /**
     * Cek exception utk mengubah attribute lainnya.
     *
     * @return void
     * @throws Throwable
     */
    private function checkException(): void
    {
        $exception = $this->exception;

        // jika kosong
        if (empty($exception)) {
            return;
        }

        if ($exception instanceof ServiceException) {
            $this->message = $exception->getMessage();
            $this->code = $exception->getErrorCode();
            $this->title = $exception->getTitle();
            $this->id = $exception->getCode();
            $this->exception = null;
            return;
        }

        $this->checkDatabaseException();

        if (
            $exception instanceof \Exception &&
            !($exception instanceof QueryException) // bukan query exception
        ) {
            $code = $exception->getCode();

            if ($code === self::SQLSTATE_VIOLATION_UNIQUE) {
                $this->code = self::SQLSTATE_VIOLATION_UNIQUE;
                $errorMessage = $exception->getMessage();

                if (json_decode($errorMessage) !== null) {
                    $errorMessage = json_decode($errorMessage, true);
                    $this->message = $errorMessage['defaultMessage'];
                }
            } elseif ($code === self::SQLSTATE_VIOLATION_FK) {
                $this->code = self::SQLSTATE_VIOLATION_FK;
                $this->message = $exception->getMessage();
            }
        }
    }

    /**
     * Cek QueryException
     *
     * @return void
     * @throws Throwable
     */
    private function checkDatabaseException(): void
    {
        // cek exception
        $exception = $this->exception;
        if (empty($exception) || !($exception instanceof QueryException || $exception instanceof UniqueConstraintViolationException)) {
            return;
        }

        // re-throw exception
        if (config('app.debug')) {
            throw $exception;
        }

        $this->code = $exception->getCode();
        $message = $exception->getMessage();

        if ($this->code == self::SQLSTATE_VIOLATION_FK) {
            $table = Str::before(Str::after($message, 'is still referenced from table "'), '"');
            if (empty($table)) {
                return;
            }

            $nav = Navigation::getInstance();
            $resource = Language::resourceName($nav->module, $table, true);

            $this->message = CoreErrorEnum::Referenced->message($resource);
            return;
        }

        if ($this->code == self::SQLSTATE_VIOLATION_UNIQUE) {
            $this->message = CoreErrorEnum::Duplicate->message();
            return;
        }
    }
}
