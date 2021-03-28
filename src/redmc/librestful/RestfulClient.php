<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\Server;
use redmc\librestful\request\Get;
use redmc\librestful\request\Post;

class RestfulClient {
    protected Server $server;
    protected string $baseURL;
    protected array $headers;

    public function __construct(Server $server, string $baseURL, array $headers) {
        $this->server = $server;
        $this->baseURL = rtrim($baseURL, "/") . "/";
        $this->headers = $headers;
    }

    public function get(): Get{
        return new Get($this->server, $this->baseURL, $this->headers);
    }

    public function post(): Post{
        return new Post($this->server, $this->baseURL, $this->headers);
    }
}