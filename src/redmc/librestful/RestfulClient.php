<?php

declare(strict_types=1);

namespace redmc\librestful;

use redmc\librestful\request\Get;
use redmc\librestful\request\Post;

class RestfulClient {
    protected string $baseURL;
    protected array $headers;

    public function __construct(string $baseURL, array $headers) {
        $this->baseURL = rtrim($baseURL, "/") . "/";
        $this->headers = $headers;
    }

    public function get(): Get{
        return new Get($this->baseURL, $this->headers);
    }

    public function post(): Post{
        return new Post($this->baseURL, $this->headers);
    }
}