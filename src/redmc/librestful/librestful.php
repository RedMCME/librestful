<?php

declare(strict_types=1);

namespace redmc\librestful;

final class librestful {
    public static function create(string $baseUrl, array $headers = []): RestfulClient{
        return new RestfulClient($baseUrl, $headers);
    }
}