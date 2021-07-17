<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Method;
use redmc\librestful\Utils;

abstract class Get extends Request{
    protected array $parameters = [];

    public function getMethod(): Method{
        return Method::GET();
    }

    public function executeFn(): callable{
        return static function (
            string $url,
            int $timeout,
            array $headers
        ): ?InternetRequestResult{
            $error = null;
            $result = Internet::getURL(
                $url,
                $timeout,
                Utils::fixedHeaders($headers),
                $error
            );

            if($error !== null){
                throw new RequestErrorException($error);
            }

            return $result;
        };
    }

    public function getUrl(): string{
        return $this->baseURL .
            $this->endpoint() .
            (!empty($this->parameters)
                ? "?" . http_build_query($this->parameters)
                : "");
    }

    public function executeParams(): array{
        return [$this->getURL(), $this->timeout, $this->headers];
    }

    public function __serialize(): array{
        $data = parent::__serialize();

        $data["parameters"] = $this->parameters;
        return $data;
    }
}
