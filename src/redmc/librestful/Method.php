<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\utils\EnumTrait;

/**
 * @method static self GET()
 * @method static self POST()
 * @method static self DELETE()
 */
final class Method{
    use EnumTrait;

    protected static function setup(): void{
        self::registerAll(
            new self("get"),
            new self("post"),
            new self("delete")
        );
    }
}
