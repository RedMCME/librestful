<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use Exception;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\TextFormat;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Method;
use redmc\librestful\Response;
use redmc\librestful\thread\ConnectorLayer;
use redmc\librestful\Utils;

abstract class Request {
    protected string $baseURL;
    protected string $endpoint = '';

    protected int $timeout = 10;
    protected array $headers = [];

    protected ?\Closure $handle = null;
    protected ?\Closure $fail = null;
    protected ?\Closure $finally = null;

    private ConnectorLayer $layer;

    public function __construct(ConnectorLayer $layer, string $baseURL, array $headers = []) {
        $this->baseURL = $baseURL;
        $this->headers = $headers;
        $this->layer = $layer;
    }

    abstract public function getMethod(): Method;

    public function async(): void {
        $this->layer->execute($this, $this->handle, $this->fail, $this->finally);
    }

    public function run(): void {
        if ($this->layer->isLoggingRequests())
            $this->layer->getPlugin()->getLogger()->debug("Running request: " . $this);

        $trace = new Exception("(This is the original stack trace for the following error)");
        try {
            $result = $this->execute();
            if($this->handle !== null)
                ($this->handle)(new Response($this, $result));
        } catch(RequestErrorException $errorException) {
            if($this->fail !== null) {
                ($this->fail)($errorException);
            } elseif ($this->layer->isLoggingRequests()) {
                $this->layer->getPlugin()->getLogger()->error($errorException->getMessage());
                $this->layer->getPlugin()->getLogger()->debug("Stack trace: " . $trace->getTraceAsString());
            }
        } finally {
            if ($this->finally !== null)
                ($this->finally)();
        }
    }

    /* @internal */
    abstract public function execute(): ?InternetRequestResult;

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
        $this->fail = $fail;
        return $this;
    }

    public function finally(?\Closure $finally): self{
        $this->finally = $finally;
        return $this;
    }

    public function getHandleCallback(): ?\Closure {
        return $this->handle;
    }

    public function getFailCallback(): ?\Closure {
        return $this->fail;
    }

    public function getFinalCallback(): ?\Closure {
        return $this->fail;
    }

    public function __serialize(): array {
        return [
            "timeout" => $this->timeout,
            "baseURL" => $this->baseURL,
            "endpoint" => $this->endpoint,
            "headers" => $this->headers
        ];
    }

    public function __toString() {
        return sprintf(
            TextFormat::DARK_GRAY . 'method="' . TextFormat::GRAY . '%s' . TextFormat::DARK_GRAY . '" ' .
            TextFormat::DARK_GRAY . 'target="' . TextFormat::GRAY . '%s' . TextFormat::DARK_GRAY . '" ' .
            TextFormat::DARK_GRAY . 'timeout=' . TextFormat::GRAY . '%d ' .
            TextFormat::DARK_GRAY . 'headers=' . TextFormat::GRAY . '[%s]' . TextFormat::RESET,
            $this->getMethod()->name(), $this->baseURL . $this->endpoint, $this->timeout, implode(" - ", Utils::fixedHeaders($this->headers))
        );
    }
}