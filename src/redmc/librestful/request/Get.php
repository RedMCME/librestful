<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Method;
use redmc\librestful\Utils;

class Get extends Request {
    protected array $parameters = [];

    public function getMethod(): Method {
        return Method::GET();
    }

    public function param(string $key, $value): self {
        $this->parameters[$key] = $value;
        return $this;
    }

    public function params(array $params): self {
        $this->parameters = array_merge($this->parameters, $params);
        return $this;
    }

    public function execute(): ?InternetRequestResult {
        $error = null;
        $result = Internet::getURL(
            $this->baseURL .
                $this->endpoint .
                (!empty($this->parameters)
                    ? '?' . http_build_query($this->parameters)
                    : ''),
            $this->timeout,
            Utils::fixedHeaders($this->headers),
            $error
        );

        if ($error !== null) {
            throw new RequestErrorException($error, $this);
        }

        return $result;
    }

    public function __serialize(): array {
        $data = parent::__serialize();

        $data['parameters'] = $this->parameters;
        return $data;
    }
}
