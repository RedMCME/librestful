<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

use redmc\librestful\exceptions\QueueShutdownException;
use Threaded;
use function serialize;

class RequestSendQueue extends Threaded{
    private bool $invalidated = false;

    private Threaded $requests;
    private Threaded $executors;
    private Threaded $executorsParams;

    public function __construct(){
        $this->requests = new Threaded();
        $this->executors = new Threaded();
        $this->executorsParams = new Threaded();
    }

    public function scheduleQuery(
        int $requestId,
        callable $execute,
        array $executeParams
    ): void{
        if($this->invalidated){
            throw new QueueShutdownException(
                "You cannot schedule a request on an invalidated queue."
            );
        }
        $this->synchronized(function () use (
            $requestId,
            $execute,
            $executeParams
        ): void{
            $this->requests[] = serialize($requestId);
            $this->executors[$requestId] = $execute;
            $this->executorsParams[$requestId] = serialize($executeParams);

            $this->notifyOne();
        });
    }

    public function fetchQuery(): ?array{
        return $this->synchronized(function (): ?array{
            while($this->requests->count() === 0 && !$this->isInvalidated()){
                $this->wait();
            }

            if ($this->isInvalidated()) {
                return null;
            }

            $requestId = unserialize($this->requests->shift());
            $executor = $this->executors[$requestId];
            $params = unserialize($this->executorsParams[$requestId]);

            unset(
                $this->executors[$requestId],
                $this->executorsParams[$requestId]
            );
            return [$requestId, $executor, $params];
        });
    }

    public function invalidate(): void{
        $this->synchronized(function (): void{
            $this->invalidated = true;
            $this->notify();
        });
    }

    /**
     * @return bool
     */
    public function isInvalidated(): bool{
        return $this->invalidated;
    }
}
