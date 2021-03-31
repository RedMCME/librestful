<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\InternetRequestResult;
use pocketmine\world\World;

class Response{
    protected InternetRequestResult $result;

    /** @var Player[] */
    public array $onlinePlayers = [];
    /** @var string[] */
    protected array $players = [];

    /** @var World[] */
    public array $loadedWorlds = [];
    /** @var int[] */
    protected array $worlds = [];

    public function __construct(InternetRequestResult $result, array $players = [], array $worlds = []) {
        $this->result = $result;
        $this->players = $players;
        $this->worlds = $worlds;

        foreach($players as $username){
            $player = Server::getInstance()->getPlayerExact($username);
            if ($player !== null && $player->isOnline()) {
                $this->onlinePlayers[$username] = $player;
            }
        }

        foreach($worlds as $id){
            $world = Server::getInstance()->getWorldManager()->getWorld($id);
            if ($world !== null && !$world->isClosed()) {
                $this->loadedWorlds[$id] = $world;
            }
        }
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

    public function loadedWorlds(): array{
        return $this->loadedWorlds;
    }

    public function worlds(): array{
        return $this->worlds;
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
