<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\Server;
use pocketmine\utils\TextFormat;
use redmc\librestful\request\Request;

final class librestful {
    public static function create(string $baseUrl, array $baseHeaders = [], ?\Closure $defaultFailCallback = null): RestfulClient{
        if ($defaultFailCallback === null) {
            $defaultFailCallback = fn(string $error, Request $request) => Server::getInstance()->getLogger()->error(sprintf(TextFormat::GRAY . "[%s] " . TextFormat::RED . "%s", $request, $error));
        }
        return new RestfulClient($baseUrl, $baseHeaders, $defaultFailCallback);
    }
}