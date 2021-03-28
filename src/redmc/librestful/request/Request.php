<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\Server;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\Method;
use redmc\librestful\RequestTask;

abstract class Request {
    protected Server $server;

    protected string $baseURL;
    protected string $endpoint;

    protected int $timeout = 10;
    protected array $headers;

    protected ?\Closure $handle = null;
    protected ?\Closure $onFail = null;

    public function __construct(Server $server, string $baseURL, array $headers = []) {
        $this->server = $server;
        $this->baseURL = $baseURL;
        $this->headers = $headers;
    }

    abstract public function getMethod(): Method;

    public function async(): void {
        $this->server->getAsyncPool()->submitTask(new RequestTask($this, $this->handle, $this->onFail));
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
                ($this->handle)($result);
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

    public function __serialize(): array {
        return [
            "timeout" => $this->timeout,
            "baseURL" => $this->baseURL,
            "endpoint" => $this->endpoint,
            "headers" => $this->headers,
        ];
    }
}