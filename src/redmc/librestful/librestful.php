<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\plugin\Plugin;
use redmc\librestful\thread\ConnectorLayer;
use redmc\librestful\thread\RequestThreadPool;

final class librestful
{
    public static function create(
        Plugin $plugin,
        string $baseUrl,
        array $baseHeaders = [],
        int $threads = 2,
        bool $logs = true
    ): RestfulClient {
        $pool = new RequestThreadPool($threads);
        return new RestfulClient(
            new ConnectorLayer($plugin, $pool, $logs),
            $baseUrl,
            $baseHeaders
        );
    }
}
