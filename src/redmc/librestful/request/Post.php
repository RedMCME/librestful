<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Method;
use redmc\librestful\Utils;

abstract class Post extends Request {
    protected array $fields = [];

    public function getMethod(): Method {
        return Method::POST();
    }

    public function execute(): ?InternetRequestResult {
        $error = null;
        $result = Internet::postURL(
            $this->baseURL . $this->endpoint(),
            $this->fields,
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

        $data['fields'] = $this->fields;
        return $data;
    }
}
