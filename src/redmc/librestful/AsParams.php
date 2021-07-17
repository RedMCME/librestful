<?php

namespace redmc\librestful;

class AsParams{
    private array $value;

    public function __construct(array $value){
        $this->value = $value;
    }

    public function getValue(): array{
        return $this->value;
    }

    public static function from(...$value): AsParams{
        return new AsParams($value);
    }
}
