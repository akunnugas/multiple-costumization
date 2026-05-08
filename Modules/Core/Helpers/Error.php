<?php

namespace Modules\Core\Helpers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class Error
{
    const AUTHENTICATION = 401;
    const DEFAULT = 590;
    const SQLSTATE_VIOLATION_FK = 23503; // error CODE SQLSTATE foreign key violation
    const SQLSTATE_VIOLATION_UNIQUE = 23505; // error CODE SQLSTATE unique violation

    /**
     * Konstruktor (menggunakan promotion).
     */
    public function __construct(public $message = null, public $code = null, public $exception = null, public $callback = null)
    {
        if (!isset($code)) {
            $this->code = static::DEFAULT;
        }

        // check for production
        $isDebug = env('APP_DEBUG');

        if (!isset($message)) {
            if ($isDebug) {
                $this->message = $this->defineMessage();
            } else {
                $this->message = 'Terjadi kesalahan';
            }
        }
    }

    /**
     * Redirect kembali ke halaman sebelumnya
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectBack()
    {
        // validation exception
        if ($this->exception instanceof ValidationException) {
            throw $this->exception;
        }

        // error lain
        $data = ['error' => $this->message];

        if (!empty($this->exception) && $this->exception instanceof QueryException) {
            $data['debug'] = [
                'message' => $this->exception->getMessage(),
                'query' => $this->exception->getSql(),
                'bindings' => $this->exception->getBindings(),
            ];
        }

        return back()->withInput()->with($data);
    }

    /**
     * Redirect kembali ke halaman sebelumnya
     *
     * @param mixed $data
     * @param string $action
     *
     * @return array
     */
    public static function showAlert($data, $action)
    {
        // alert sukses jika bukan error
        if (!static::isError($data)) {
            return [
                'type' => 'success',
                'message' => $action . ' berhasil',
            ];
        }

        // validation exception
        if ($data->exception instanceof ValidationException) {
            throw $data->exception;
        }

        // alert error
        return [
            'type' => 'error',
            'message' => $action . ' gagal',
        ];
    }

    /**
     * Apakah error?
     *
     * @param mixed $data
     *
     * @return bool
     */
    public static function isError($data)
    {
        return $data instanceof static;
    }

    /**
     * Jika error kembalikan NULL
     *
     * @param mixed $data
     *
     * @return bool
     */
    public static function returnValue($data)
    {
        return static::isError($data) ? NULL : $data;
    }

    /**
     * Pesan error berdasarkan kode error.
     *
     * @return string
     */
    private function defineMessage()
    {
        if ($this->message) {
            return $this->message;
        }

        // cek jika page or action forbidden
        if (!empty($this->exception) && $this->exception instanceof AuthorizationException) {
            $this->code = 403;
            return $this->exception->getMessage();
        }

        // cek sqlstate unique violation
        if (!empty($this->exception) && $this->exception instanceof \Exception) {
            $code = $this->exception->getCode();
            if ($code === self::SQLSTATE_VIOLATION_UNIQUE) { // terusan dari ModelValidation terkait method validateUniqueComposite()
                $this->code = static::SQLSTATE_VIOLATION_UNIQUE;
                $errorMessage = $this->exception->getMessage();

                if (json_decode($errorMessage) !== null) {
                    $errorMessage = json_decode($errorMessage, true);
                    return $errorMessage['defaultMessage'];
                }
            } elseif ($code === self::SQLSTATE_VIOLATION_FK) {
                $this->code = static::SQLSTATE_VIOLATION_FK;
                return $this->exception->getMessage();
            }
        }

        return Response::$statusTexts[$this->code] ?? 'Terjadi kesalahan';
    }
}
