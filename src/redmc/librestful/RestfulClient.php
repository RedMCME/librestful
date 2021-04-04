<?php

declare(strict_types=1);

namespace redmc\librestful;

use redmc\librestful\request\Get;
use redmc\librestful\request\Post;

class RestfulClient {
    protected string $baseURL;
    protected array $headers;
    private ?\Closure $defaultFailCallback;

    public function __construct(string $baseURL, array $headers, ?\Closure $defaultFailCallback = null) {
        $this->baseURL = rtrim($baseURL, "/") . "/";
        $this->headers = $headers;
        $this->defaultFailCallback = $defaultFailCallback;
    }

    public function get(): Get{
        return new Get($this->baseURL, $this->headers, $this->defaultFailCallback);
    }

    public function post(): Post{
        return new Post($this->baseURL, $this->headers, $this->defaultFailCallback);
    }
}