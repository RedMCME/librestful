<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\Server;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\Method;
use redmc\librestful\RequestTask;
use redmc\librestful\Response;

abstract class Request {
    protected string $baseURL;
    protected string $endpoint = '';

    protected int $timeout = 10;
    protected array $headers = [];
    /** @var string[] */
    protected array $players = [];
    /** @var int[] */
    protected array $worlds = [];

    protected bool $abortIfNoPlayer = false;
    protected bool $abortIfNoWorld = false;

    protected ?\Closure $handle = null;
    protected ?\Closure $onFail = null;

    public function __construct(string $baseURL, array $headers = []) {
        $this->baseURL = $baseURL;
        $this->headers = $headers;
    }

    abstract public function getMethod(): Method;

    public function async(): void {
        Server::getInstance()->getAsyncPool()->submitTask(new RequestTask($this, $this->handle, $this->onFail));
    }

    public function run(): void {
        $error = null;
        $result = $this->execute($error);
        if($error !== null) {
            if($this->onFail !== null) {
                ($this->onFail)($error);
            }
        } else {
            if($this->handle !== null) {
                ($this->handle)(new Response($result, $this->players, $this->worlds));
            }
        }
    }

    /* @internal */
    abstract public function execute(?string &$error = null): ?InternetRequestResult;

    public function endpoint(string $endpoint): self {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function timeout(int $timeout): self {
        $this->timeout = $timeout;
        return $this;
    }

    public function header(string $key, $value): self {
        $this->headers[$key] = $value;
        return $this;
    }

    public function headers(array $headers): self {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function player(string $username): self {
        $this->players[] = $username;
        return $this;
    }

    public function players(array $players): self {
        $this->players[] = array_merge($this->players, $players);
        return $this;
    }

    public function world(int $worldId): self {
        $this->worlds[] = $worldId;
        return $this;
    }

    public function result(?\Closure $handle): self {
        $this->handle = $handle;
        return $this;
    }

    public function fail(?\Closure $fail): self {
        $this->onFail = $fail;
        return $this;
    }

    public function getHandleCallback(): ?\Closure {
        return $this->handle;
    }

    public function getFailCallback(): ?\Closure {
        return $this->onFail;
    }

    public function getPlayers(): array{
        return $this->players;
    }

    public function getWorlds(): array{
        return $this->worlds;
    }

    public function abortIfNoPlayer(bool $state = true): self{
        $this->abortIfNoPlayer = $state;
        return $this;
    }

    public function abortIfNoWorld(bool $state = true): self{
        $this->abortIfNoWorld = $state;
        return $this;
    }

    public function willAbortIfNoWorld(): bool{ return $this->abortIfNoWorld; }
    public function willAbortIfNoPlayer(): bool{ return $this->abortIfNoPlayer; }

    public function __serialize(): array {
        return [
            "timeout" => $this->timeout,
            "baseURL" => $this->baseURL,
            "endpoint" => $this->endpoint,
            "headers" => $this->headers,
            "players" => $this->players,
            "worlds" => $this->worlds
        ];
    }
}