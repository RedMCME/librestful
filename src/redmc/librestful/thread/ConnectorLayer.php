<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

use Exception;
use pocketmine\plugin\Plugin;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\request\Request;
use function str_replace;
use function usleep;

class ConnectorLayer
{
    private Plugin $plugin;

    private RequestThread $requestThread;

    private bool $loggingRequests;

    /** @var callable[] */
    private array $handlers = [];

    private int $requestId = 0;

    public function __construct(
        Plugin $plugin,
        RequestThread $requestThread,
        bool $logRequests = false
    )
    {
        $this->plugin = $plugin;
        if ($requestThread instanceof RequestThreadPool) {
            $requestThread->setConnectorLayer($this);
        }

        $this->requestThread = $requestThread;
        $this->loggingRequests = $logRequests;
    }

    public function setLoggingRequests(bool $loggingRequests): void
    {
        $this->loggingRequests = $loggingRequests;
    }

    public function isLoggingRequests(): bool
    {
        return $this->loggingRequests;
    }

    public function execute(Request $request, ?callable $onResult): void
    {
        $requestId = $this->requestId++;

        $this->handlers[$requestId] =
            function ($result) use ($request, $onResult) {
                if ($result instanceof RequestErrorException) {
                    $request->failed($result);
                } else {
                    $request->success($result);
                }

                $request->finally();
                if ($onResult !== null) {
                    $onResult($request->result());
                }
            };
        if ($this->loggingRequests) {
            $this->plugin
                ->getLogger()
                ->debug(
                    'Queuing request: ' .
                    str_replace(
                        ["\r\n", "\n"],
                        "\\n ",
                        $request->__toString()
                    )
                );
        }
        $this->requestThread->addRequest($requestId, $request);
    }

    private function reportError(
        ?callable $failedHandler,
        RequestErrorException $error,
        ?Exception $trace
    ): void
    {
        if ($failedHandler !== null) {
            try {
                $failedHandler($error);
                $error = null;
            } catch (Exception $err) {
                $error = $err;
            }
        }
        if ($error !== null) {
            $this->plugin->getLogger()->error($error->getMessage());
            if ($trace !== null) {
                $this->plugin
                    ->getLogger()
                    ->debug('Stack trace: ' . $trace->getTraceAsString());
            }
        }
    }

    public function waitAll(): void
    {
        while (!empty($this->handlers)) {
            $this->checkResults();
            usleep(1000);
        }
    }

    public function checkResults(): void
    {
        $this->requestThread->readResults($this->handlers);
    }

    public function close(): void
    {
        $this->requestThread->stopRunning();
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}
