<?php

declare(strict_types=1);

namespace redmc\librestful\exceptions;

use redmc\librestful\request\Request;
use RuntimeException;
use Throwable;

class RequestErrorException extends RuntimeException {
    private ?Request $request;

    public function __construct(
        $message = '',
        ?Request $request = null,
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->request = $request;
    }

    public function request(): ?Request {
        return $this->request;
    }
}
