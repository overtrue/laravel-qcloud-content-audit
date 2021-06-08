<?php

namespace Overtrue\LaravelQcs\Exceptions;

use Throwable;

class InvalidImageException extends Exception
{
    public array $response;

    public function __construct(array $response, Throwable $previous = null)
    {
        $this->response = $response;

        parent::__construct('Invalid image', 422, $previous);
    }
}
