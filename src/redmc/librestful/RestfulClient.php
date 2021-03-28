<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\Server;

class RestfulClient {
    protected Server $server;
    protected string $baseURL;
    protected array $headers;

    public function __construct(Server $server, string $baseURL, array $headers) {
        $this->server = $server;
        $this->baseURL = rtrim($baseURL, "/") . "/";
        $this->headers = $headers;
    }

    public function get(string $endpoint, ?\Closure $handle = null, ?\Closure $onFail = null, int $timeout = 10, array $headers = []): void{
        $this->server->getAsyncPool()->submitTask(new QueryTask(
            QueryTask::METHOD_GET, $this->baseURL . $endpoint, $timeout, $this->fixedHeaders($headers), [], $handle, $onFail
            )
        );
    }

    public function post(string $endpoint, array $args = [], ?\Closure $handle = null, ?\Closure $onFail = null, int $timeout = 10, array $headers = []): void{
        $this->server->getAsyncPool()->submitTask(new QueryTask(
                QueryTask::METHOD_POST, $this->baseURL . $endpoint, $timeout, $this->fixedHeaders($headers), $args, $handle, $onFail
            )
        );
    }

    private function fixedHeaders(array $headers): array{
        $headers = array_merge($this->headers, $headers);
        $fixed = [];

        foreach($headers as $key => $value){
            $fixed[] = sprintf("%s: %s", $key, $value);
        }

        return $fixed;
    }
}