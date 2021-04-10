<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

use redmc\librestful\exceptions\QueueShutdownException;
use redmc\librestful\request\Request;
use Threaded;
use function serialize;

class RequestSendQueue extends Threaded{

    private bool $invalidated = false;

    private Threaded $requests;

    public function __construct(){
        $this->requests = new Threaded();
    }

    public function scheduleQuery(int $requestId, Request $request) : void{
        if($this->invalidated){
            throw new QueueShutdownException("You cannot schedule a request on an invalidated queue.");
        }
        $this->synchronized(function() use ($requestId, $request) : void{
            $this->requests[] = serialize([$requestId, $request]);
            $this->notifyOne();
        });
    }

    public function fetchQuery() : ?string {
        return $this->synchronized(function(): ?string {
            while($this->requests->count() === 0 && !$this->isInvalidated()){
                $this->wait();
            }
            return $this->requests->shift();
        });
    }

    public function invalidate() : void {
        $this->synchronized(function():void{
            $this->invalidated = true;
            $this->notify();
        });
    }

    /**
     * @return bool
     */
    public function isInvalidated(): bool {
        return $this->invalidated;
    }
}
