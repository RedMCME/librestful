<?php

declare(strict_types=1);

namespace redmc\librestful;

class Utils{
    public static function fixedHeaders(array $headers): array{
        $fixed = [];

        foreach($headers as $key => $value){
            $fixed[] = sprintf("%s: %s", $key, $value);
        }

        return $fixed;
    }
}
