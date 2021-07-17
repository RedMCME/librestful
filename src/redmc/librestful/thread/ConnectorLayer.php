<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

use pocketmine\plugin\Plugin;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\request\Request;
use redmc\librestful\Response;
use function str_replace;
use function usleep;

class ConnectorLayer{
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
    ){
        $this->plugin = $plugin;
        if($requestThread instanceof RequestThreadPool){
            $requestThread->setConnectorLayer($this);
        }

        $this->requestThread = $requestThread;
        $this->loggingRequests = $logRequests;
    }

    public function setLoggingRequests(bool $loggingRequests): void{
        $this->loggingRequests = $loggingRequests;
    }

    public function isLoggingRequests(): bool{
        return $this->loggingRequests;
    }

    public function execute(
        Request $request,
        callable $execute,
        array $executeParams,
        ?callable $onResult
    ): void{
        $requestId = $this->requestId++;

        $this->handlers[$requestId] = function ($response) use (
            $request,
            $onResult
        ){
            $result = null;
            if($response instanceof RequestErrorException){
                $result = $request->failed($response);
            }elseif($response instanceof Response){
                $code = $response->code();
                if($code >= 200 && $code <= 299){
                    $result = $request->success($response);
                }elseif($code >= 400 && $code <= 499){
                    $result = $request->clientError($response);
                }elseif($code >= 500 && $code <= 599){
                    $result = $request->serverError($response);
                }else{
                    $result = $request->failed(
                        new RequestErrorException("unknown response: " . $code)
                    );
                }
            }

            if($result !== null){
                $request->setResult($result);
            }

            $request->finally();
            if($onResult !== null){
                $onResult($request->result());
            }
        };
        if($this->loggingRequests){
            $this->plugin
                ->getLogger()
                ->debug(
                    "Queuing request: " .
                    str_replace(
                        ["\r\n", "\n"],
                        "\\n ",
                        $request->__toString()
                    )
                );
        }
        $this->requestThread->addRequest($requestId, $execute, $executeParams);
    }

    public function waitAll(): void{
        while(!empty($this->handlers)){
            $this->checkResults();
            usleep(1000);
        }
    }

    public function checkResults(): void{
        $this->requestThread->readResults($this->handlers);
    }

    public function close(): void{
        $this->requestThread->stopRunning();
    }

    public function getPlugin(): Plugin{
        return $this->plugin;
    }
}
