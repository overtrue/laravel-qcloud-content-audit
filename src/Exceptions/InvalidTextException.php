<?php

namespace Overtrue\LaravelQcloudContentAudit\Exceptions;

use Throwable;

class InvalidTextException extends Exception
{
    public string $contents;

    public array $response;

    public function __construct(string $message, string $contents, array $response, ?Throwable $previous = null)
    {
        $this->contents = $contents;
        $this->response = $response;

        parent::__construct($message, 422, $previous);
    }
}
