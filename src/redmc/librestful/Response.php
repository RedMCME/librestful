<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\InternetRequestResult;

class Response{
    protected InternetRequestResult $result;

    /** @var Player[] */
    protected array $onlinePlayers = [];
    /** @var string[] */
    protected array $players = [];

    public function __construct(InternetRequestResult $result, array $players = []) {
        $this->result = $result;
        $this->players = $players;

        foreach($players as $username){
            $player = Server::getInstance()->getPlayerExact($username);
            if ($player !== null && $player->isOnline()) {
                $this->onlinePlayers[$username] = $player;
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
        return $this->playersOnline[$username] ?? null;
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
