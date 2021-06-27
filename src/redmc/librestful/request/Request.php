<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\Server;
use pocketmine\utils\TextFormat;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Method;
use redmc\librestful\Response;
use redmc\librestful\RestfulClient;
use redmc\librestful\Result;
use redmc\librestful\thread\ConnectorLayer;
use redmc\librestful\Utils;

abstract class Request{

    protected string $baseURL;

    protected int $timeout = 10;
    protected array $headers = [];

    private ConnectorLayer $layer;
    protected ?\Closure $onResult = null;

    private Result $result;

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

    /* Status code: 400 - 499, Example: Bad request, Not Found */
    public function clientError(Response $response): ?Result{
        Server::getInstance()->getLogger()->error("Client error respond: " . $response->body() . " (Status code: " . $response->code() . ")");
        return null;
    }

    /* Status code: 500 - 599, Example: Internal Server Error, Bad Gateway */
    public function serverError(Response $response): ?Result{
        Server::getInstance()->getLogger()->error("Server error respond: " . $response->body() . " (Status code: " . $response->code() . ")");
        return null;
    }

    /* Status code: 200 - 299, Example: Status OK, Created */
    abstract public function success(Response $response): ?Result;

    /* Request failed */
    abstract public function failed(RequestErrorException $error): ?Result;

    /* @internal */
    public function finally(): void{}

    abstract protected function endpoint(): string;

    public function setResult(Result $result): void{
        $this->result = $result;
    }

    public function result(): Result{
        return $this->result;
    }

    public function getURL(): string{
        return $this->baseURL . $this->endpoint();
    }

    public function async(): void{
        $this->layer->execute(
            $this,
            $this->executeFn(),
            $this->executeParams(),
            $this->onResult
        );
    }

    public function sync(): self{
        if($this->layer->isLoggingRequests()){
            $this->layer
                ->getPlugin()
                ->getLogger()
                ->debug('Running request: ' . $this);
        }

        try{
            $start = microtime(true);
            $result = ($this->executeFn())(...$this->executeParams());
            $response = new Response($result, microtime(true) - $start);

            $code = $result->code();
            if($code >= 200 && $code <= 299){
                $result = $this->success($response);
            }else if($code >= 400 && $code <= 499){
                $result = $this->clientError($response);
            }else if($code >= 500 && $code <= 599){
                $result = $this->serverError($response);
            }else{
                $result = $this->failed(new RequestErrorException("unknown response: " . $code));
            }

            if($result !== null){
                $this->setResult($result);
            }
        }catch(RequestErrorException $errorException){
            $this->failed($errorException);
        }finally{
            $this->finally();
        }
        if($this->onResult !== null){
            ($this->onResult)($this->result());
        }

        return $this;
    }

    public function baseURL(string $baseURL): self{
        $this->baseURL = $baseURL;
        return $this;
    }

    public function timeout(int $timeout): self{
        $this->timeout = $timeout;
        return $this;
    }

    public function header(string $key, $value): self{
        $this->headers[$key] = $value;
        return $this;
    }

    public function headers(array $headers): self{
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function onResult(\Closure $result): self{
        $this->onResult = $result;
        return $this;
    }

    public function __serialize(): array{
        return [
            'timeout' => $this->timeout,
            'baseURL' => $this->baseURL,
            'endpoint' => $this->endpoint(),
            'headers' => $this->headers
        ];
    }

    public function __toString(){
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
