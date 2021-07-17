<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

use redmc\librestful\Response;
use Threaded;
use function is_string;
use function serialize;
use function unserialize;

class RequestRecvQueue extends Threaded{
    public function publishResult(int $requestId, Response $result): void{
        $this[] = serialize([$requestId, $result]);
    }

    public function publishError(int $requestId, \Exception $error){
        $this[] = serialize([$requestId, $error]);
    }

    public function fetchResult(&$requestId, &$result): bool{
        $row = $this->shift();
        if(is_string($row)){
            [$requestId, $result] = unserialize($row, [
                "allowed_classes" => true,
            ]);
            return true;
        }
        return false;
    }
}
