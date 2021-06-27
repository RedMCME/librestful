<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\utils\InternetRequestResult;

class Response {
    protected InternetRequestResult $result;
    private float $ms;

    public function __construct(InternetRequestResult $result, float $ms) {
        $this->result = $result;
        $this->ms = $ms;
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

    public function ms(): float{
        return $this->ms;
    }
}
