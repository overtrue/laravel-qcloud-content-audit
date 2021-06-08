<?php

namespace Overtrue\LaravelQcs\Exceptions;

use Throwable;

class InvalidTextException extends Exception
{
    public string $contents;
    public array $response;

    public function __construct(string $contents, array $response, Throwable $previous = null)
    {
        $this->contents = $contents;
        $this->response = $response;

        parent::__construct('Invalid text contents', 422, $previous);
    }
}
