<?php

namespace Modules\Core\Exceptions;

use Exception;

class ServiceException extends Exception
{
    protected $errorCode;
    protected $title;

    public function __construct(string $message = null, string $code = null, string $title = null, int $id = null)
    {
        // some code
        $this->errorCode = $code;
        $this->title = $title;

        // make sure everything is assigned properly
        parent::__construct($message, $id);
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getTitle()
    {
        return $this->title;
    }
}

