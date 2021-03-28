<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\Method;
use redmc\librestful\Utils;

class Get extends Request {

    public function getMethod(): Method{
        return Method::GET();
    }

    public function execute(?string &$error = null): ?InternetRequestResult{
        return Internet::getURL(
            $this->baseURL . $this->endpoint,
            $this->timeout,
            Utils::fixedHeaders($this->headers),
            $error
        );
    }
}