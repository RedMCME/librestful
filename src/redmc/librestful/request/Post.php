<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Method;
use redmc\librestful\Utils;

abstract class Post extends Request{
    protected array $fields = [];

    public function getMethod(): Method{
        return Method::POST();
    }

    public function executeFn(): callable{
        return static function (
            string $url,
            array $fields,
            int $timeout,
            array $headers
        ): ?InternetRequestResult{
            $error = null;
            $result = Internet::postURL(
                $url,
                $fields,
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

    public function executeParams(): array{
        return [$this->getURL(), $this->fields, $this->timeout, $this->headers];
    }

    public function __serialize(): array{
        $data = parent::__serialize();

        $data["fields"] = $this->fields;
        return $data;
    }
}
