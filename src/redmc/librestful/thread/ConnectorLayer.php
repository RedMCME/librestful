<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

use Error;
use Exception;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Terminal;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\request\Request;
use ReflectionClass;
use function array_merge;
use function array_pop;
use function count;
use function str_replace;
use function usleep;

class ConnectorLayer {
    private Plugin $plugin;

    private RequestThread $requestThread;

    private bool $loggingRequests;

    /** @var callable[] */
    private array $handlers = [];

    private int $requestId = 0;

    public function __construct(Plugin $plugin, RequestThread $requestThread, bool $logRequests = false) {
        $this->plugin = $plugin;
        if ($requestThread instanceof RequestThreadPool) {
            $requestThread->setConnectorLayer($this);
        }

        $this->requestThread = $requestThread;
        $this->loggingRequests = $logRequests;
    }

    public function setLoggingRequests(bool $loggingRequests): void {
        $this->loggingRequests = $loggingRequests;
    }

    public function isLoggingRequests(): bool {
        return $this->loggingRequests;
    }

    public function execute(Request $request, ?callable $handle, ?callable $fail, ?callable $finally): void {
        $requestId = $this->requestId++;
        $trace = new Exception("(This is the original stack trace for the following error)");

        $this->handlers[$requestId] = function($result) use ($handle, $fail, $finally, $trace) {
            if($result instanceof RequestErrorException) {
                $this->reportError($fail, $result, $trace);
            } elseif ($handle !== null) {
                try {
                    $handle($result);
                } catch(Exception $e) {
                    $prop = (new ReflectionClass(Exception::class))->getProperty("trace");
                    $prop->setAccessible(true);
                    $newTrace = $prop->getValue($e);
                    $oldTrace = $prop->getValue($trace);
                    for($i = count($newTrace) - 1, $j = count($oldTrace) - 1; $i >= 0 && $j >= 0 && $newTrace[$i] === $oldTrace[$j]; --$i, --$j) {
                        array_pop($newTrace);
                    }

                    $prop->setValue($e, array_merge($newTrace, [
                        [
                            "function" => Terminal::$COLOR_YELLOW . "--- below is the original stack trace ---" . Terminal::$FORMAT_RESET,
                        ],
                    ], $oldTrace));
                    throw $e;
                } catch(Error $e) {
                    $exceptionProperty = (new ReflectionClass(Exception::class))->getProperty("trace");
                    $exceptionProperty->setAccessible(true);
                    $oldTrace = $exceptionProperty->getValue($trace);

                    $errorProperty = (new ReflectionClass(Error::class))->getProperty("trace");
                    $errorProperty->setAccessible(true);
                    $newTrace = $errorProperty->getValue($e);

                    for($i = count($newTrace) - 1, $j = count($oldTrace) - 1; $i >= 0 && $j >= 0 && $newTrace[$i] === $oldTrace[$j]; --$i, --$j) {
                        array_pop($newTrace);
                    }

                    $errorProperty->setValue($e, array_merge($newTrace, [
                        [
                            "function" => Terminal::$COLOR_YELLOW . "--- below is the original stack trace ---" . Terminal::$FORMAT_RESET,
                        ],
                    ], $oldTrace));
                    throw $e;
                }
            }
            if ($finally !== null) {
                $finally();
            }
        };
        if($this->loggingRequests) {
            $this->plugin->getLogger()->debug("Queuing request: " . str_replace(["\r\n", "\n"], "\\n ", $request->__toString()));
        }
        $this->requestThread->addRequest($requestId, $request);
    }

    private function reportError(?callable $default, RequestErrorException $error, ?Exception $trace): void {
        if($default !== null) {
            try {
                $default($error, $trace);
                $error = null;
            } catch(Exception $err) {
                $error = $err;
            }
        }
        if($error !== null) {
            $this->plugin->getLogger()->error($error->getMessage());
            if($trace !== null) {
                $this->plugin->getLogger()->debug("Stack trace: " . $trace->getTraceAsString());
            }
        }
    }

    public function waitAll(): void {
        while(!empty($this->handlers)) {
            $this->checkResults();
            usleep(1000);
        }
    }

    public function checkResults(): void {
        $this->requestThread->readResults($this->handlers);
    }

    public function close(): void {
        $this->requestThread->stopRunning();
    }

    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}
