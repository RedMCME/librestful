<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\utils\InternetRequestResult;
use redmc\librestful\request\Request;

class Response {
    protected InternetRequestResult $result;

    private Request $request;

    public function __construct(
        Request $request,
        InternetRequestResult $result
    ) {
        $this->result = $result;
        $this->request = $request;
    }

    public function result(): InternetRequestResult {
        return $this->result;
    }

    public function code(): int {
        return $this->result->getCode();
    }

    public function body(): string {
        return $this->result->getBody();
    }

    /** @return string[][] */
    public function headers(): array {
        return $this->result->getHeaders();
    }

    public function request(): Request {
        return $this->request;
    }
}
