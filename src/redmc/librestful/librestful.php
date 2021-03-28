<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\Server;

final class librestful {
    public static function create(Server $server, string $baseUrl, array $headers = []): RestfulClient{
        return new RestfulClient($server, $baseUrl, $headers);
    }
}