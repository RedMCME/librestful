<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\InternetRequestResult;
use pocketmine\world\World;

class Response{
    protected InternetRequestResult $result;

    public function __construct(InternetRequestResult $result) {
        $this->result = $result;
    }

    public function onlinePlayers(): array{
        return $this->onlinePlayers;
    }

    public function players(): array{
        return $this->players;
    }

    public function player(string $username): ?Player{
        return $this->onlinePlayers[$username] ?? null;
    }

    public function world(int $id): ?World{
        return $this->loadedWorlds[$id] ?? null;
    }

    public function result(): InternetRequestResult{
        return $this->result;
    }

    public function code(): int{
        return $this->result->getCode();
    }

    public function body(): string{
        return $this->result->getBody();
    }

    /** @return string[][] */
    public function headers(): array{
        return $this->result->getHeaders();
    }
}
