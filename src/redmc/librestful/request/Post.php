<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\Method;
use redmc\librestful\Utils;

class Post extends Request {
    protected array $fields = [];

    public function getMethod(): Method{
        return Method::POST();
    }

    public function field(string $key, $value): self{
        $this->fields[$key] = $value;
        return $this;
    }

    public function fields(array $fields): self{
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }

    public function execute(?string &$error = null): ?InternetRequestResult{
        return Internet::postURL(
            $this->baseURL . $this->endpoint,
            $this->fields,
            $this->timeout,
            Utils::fixedHeaders($this->headers),
            $error
        );
    }

    public function __serialize(): array {
        $data = parent::__serialize();

        $data["fields"] = $this->fields;
        return $data;
    }
}