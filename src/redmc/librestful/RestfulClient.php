<?php

declare(strict_types=1);

namespace redmc\librestful;

use redmc\librestful\request\Request;
use redmc\librestful\thread\ConnectorLayer;

class RestfulClient{
    protected string $baseURL;
    protected array $headers;
    private ConnectorLayer $layer;

    public function __construct(
        ConnectorLayer $layer,
        string $baseURL,
        array $headers
    ){
        $this->baseURL = rtrim($baseURL, "/") . "/";
        $this->headers = $headers;
        $this->layer = $layer;
    }

    public function getLayer(): ConnectorLayer{
        return $this->layer;
    }

    public function getBaseURL(): string{
        return $this->baseURL;
    }

    public function getHeaders(): array{
        return $this->headers;
    }

    public function waitAll(): void{
        $this->layer->waitAll();
    }
}
