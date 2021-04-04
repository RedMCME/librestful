<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\TextFormat;
use redmc\librestful\Method;
use redmc\librestful\RequestTask;
use redmc\librestful\Response;
use redmc\librestful\Utils;

abstract class Request {
    protected string $baseURL;
    protected string $endpoint = '';

    protected int $timeout = 10;
    protected array $headers = [];

    protected ?\Closure $handle = null;
    protected ?\Closure $fail = null;
    protected ?\Closure $finally = null;

    protected int $retryIfFailedBlockTimes = 0;
    protected ?int $retryIfFailedTimes = null;
    protected ?TaskScheduler $retryScheduler = null;

    public function __construct(string $baseURL, array $headers = [], ?\Closure $defaultFailCallback = null) {
        $this->baseURL = $baseURL;
        $this->headers = $headers;
        $this->fail = $defaultFailCallback;
    }

    abstract public function getMethod(): Method;

    public function async(): int {
        Server::getInstance()->getLogger()->debug("Running request on async task " . $this);
        return Server::getInstance()->getAsyncPool()->submitTask(new RequestTask($this, $this->handle, $this->fail, $this->finally));
    }

    public function run(): void {
        Server::getInstance()->getLogger()->debug("Running request " . $this);
        $error = null;
        $result = $this->execute($error);
        if($error !== null) {
            if($this->fail !== null) {
                ($this->fail)($error, $this);
            }
            if ($this->retryIfFailedTimes !== null && ($this->retryIfFailedTimes === -1 || ($this->retryIfFailedTimes--) > 0)) {
                Server::getInstance()->getLogger()->debug("Retrying request " . $this);
                if ($this->retryIfFailedBlockTimes === 0) {
                    $this->run();
                } else {
                    $request = $this;
                    $this->retryScheduler->scheduleDelayedTask(new ClosureTask(function() use($request): void{
                        $request->run();
                    }), $this->retryIfFailedBlockTimes * 20);
                }

                return;
            }
            if ($this->finally !== null) {
                ($this->finally)();
            }
            return;
        }

        if($this->handle !== null) {
            ($this->handle)(new Response($result));
        }

        if ($this->finally !== null) {
            ($this->finally)();
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
        $this->fail = $fail;
        return $this;
    }

    public function finally(?\Closure $finally): self{
        $this->finally = $finally;
        return $this;
    }

    /**
     * @param int $times determines how many times to try. Use -1 for unlimited try.
     * @param int $blockTimes determines how long to wait between trials.
     */
    public function retryIfFailed(int $times = -1, int $blockTimes = 0, TaskScheduler $scheduler = null): self {
        if ($blockTimes < 0) {
            throw new \Exception("block times must be a positive number");
        }
        if ($times < -1 || $times === 0) {
            throw new \Exception("times must be equal -1 or greater than 0");
        }
        if ($blockTimes > 0 && $scheduler === null) {
            throw new \Exception("task scheduler required for block time");
        }

        $this->retryIfFailedBlockTimes = $blockTimes;
        $this->retryIfFailedTimes = $times;
        $this->retryScheduler = $scheduler;
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

    public function getRetryTimes(): ?int {
        return $this->retryIfFailedTimes;
    }

    /* @internal */
    public function decreaseRetryTime(int $count): void {
        $this->retryIfFailedTimes -= $count;
    }

    public function getRetryBlockTimes(): int {
        return $this->retryIfFailedBlockTimes;
    }

    public function getRetryScheduler(): ?TaskScheduler {
        return $this->retryScheduler;
    }

    public function __serialize(): array {
        return [
            "timeout" => $this->timeout,
            "baseURL" => $this->baseURL,
            "endpoint" => $this->endpoint,
            "headers" => $this->headers,
            "retryIfFailedBlockTimes" => $this->retryIfFailedBlockTimes,
            "retryIfFailedTimes" => $this->retryIfFailedTimes,
            "retryScheduler" => $this->retryScheduler,
        ];
    }

    public function __toString() {
        return sprintf(
            TextFormat::DARK_GRAY . 'target="' . TextFormat::GRAY . '%s' . TextFormat::DARK_GRAY . '" ' .
            TextFormat::DARK_GRAY . 'timeout=' . TextFormat::GRAY . '%d ' .
            TextFormat::DARK_GRAY . 'retry-times=' . TextFormat::GRAY . '%d ' .
            TextFormat::DARK_GRAY . 'retry-block-times=' . TextFormat::GRAY . '%d ' .
            TextFormat::DARK_GRAY . 'headers=' . TextFormat::GRAY . '[%s]' . TextFormat::RESET,
            $this->baseURL . $this->endpoint, $this->timeout, $this->retryIfFailedTimes, $this->retryIfFailedBlockTimes, implode(" - ", Utils::fixedHeaders($this->headers))
        );
    }
}