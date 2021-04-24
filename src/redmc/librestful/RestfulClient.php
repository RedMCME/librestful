<?php

declare(strict_types=1);

namespace redmc\librestful;

use redmc\librestful\request\Get;
use redmc\librestful\request\Post;
use redmc\librestful\thread\ConnectorLayer;

class RestfulClient {
    protected string $baseURL;
    protected array $headers;
    private ConnectorLayer $layer;

    public function __construct(
        ConnectorLayer $layer,
        string $baseURL,
        array $headers
    ) {
        $this->baseURL = rtrim($baseURL, '/') . '/';
        $this->headers = $headers;
        $this->layer = $layer;
    }

    public function get(): Get {
        return new Get($this->layer, $this->baseURL, $this->headers);
    }

    public function post(): Post {
        return new Post($this->layer, $this->baseURL, $this->headers);
    }

    public function waitAll(): void {
        $this->layer->waitAll();
    }
}
