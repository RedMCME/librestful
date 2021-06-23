<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\TextFormat;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Method;
use redmc\librestful\Response;
use redmc\librestful\RestfulClient;
use redmc\librestful\Result;
use redmc\librestful\thread\ConnectorLayer;
use redmc\librestful\Utils;

abstract class Request {

    protected string $baseURL;

    protected int $timeout = 10;
    protected array $headers = [];

    private ConnectorLayer $layer;
    protected ?\Closure $onResult = null;

    protected Result $result;

    public function __construct(){
        $this->result = new Result(null, null);
    }

    public function bind(RestfulClient $client): self{
        $this->layer = $client->getLayer();
        $this->baseURL($client->getBaseURL());
        $this->headers($client->getHeaders());
        return $this;
    }

    abstract public function getMethod(): Method;

    /* @internal */
    abstract public function executeFn(): callable;
    /* @internal */
    abstract public function executeParams(): array;

    abstract public function success(Response $response): void;
    abstract public function failed(RequestErrorException $error): void;
    public function finally(): void{}

    abstract protected function endpoint(): string;

    public function result(): Result{
        return $this->result;
    }

    public function getURL(): string{
        return $this->baseURL . $this->endpoint();
    }

    public function async(): void {
        $this->layer->execute(
            $this,
            $this->executeFn(),
            $this->executeParams(),
            $this->onResult
        );
    }

    public function sync(): self {
        if ($this->layer->isLoggingRequests()) {
            $this->layer
                ->getPlugin()
                ->getLogger()
                ->debug('Running request: ' . $this);
        }

        try {
            $start = microtime(true);
            $result = ($this->executeFn())(...$this->executeParams());

            $this->success(new Response($result, microtime(true) - $start));
        } catch (RequestErrorException $errorException) {
            $this->failed($errorException);
        } finally {
            $this->finally();
        }
        if ($this->onResult !== null) {
            ($this->onResult)($this->result());
        }

        return $this;
    }

    public function baseURL(string $baseURL): self {
        $this->baseURL = $baseURL;
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

    public function onResult(\Closure $result): self {
        $this->onResult = $result;
        return $this;
    }

    public function __serialize(): array {
        return [
            'timeout' => $this->timeout,
            'baseURL' => $this->baseURL,
            'endpoint' => $this->endpoint(),
            'headers' => $this->headers
        ];
    }

    public function __toString() {
        return sprintf(
            TextFormat::GOLD .
                'method="' .
                TextFormat::GRAY .
                '%s' .
                TextFormat::GOLD .
                '" ' .
                TextFormat::GOLD .
                'target="' .
                TextFormat::GRAY .
                '%s' .
                TextFormat::GOLD .
                '" ' .
                TextFormat::GOLD .
                'timeout=' .
                TextFormat::GRAY .
                '%d ' .
                TextFormat::GOLD .
                'headers=' .
                TextFormat::GRAY .
                '[%s]' .
                TextFormat::RESET,
            $this->getMethod()->name(),
            $this->baseURL . $this->endpoint(),
            $this->timeout,
            implode(' - ', Utils::fixedHeaders($this->headers))
        );
    }
}
