<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use redmc\librestful\request\Request;

final class RequestTask extends AsyncTask {

    private Request $request;
    private ?int $wait;

    public const HANDLE = "handle";
    public const FAIL = "fail";
    public const FINALLY = "final";

    public function __construct(Request $request, ?\Closure $handle, ?\Closure $fail, ?\Closure $finally, ?int $wait = null) {
        $this->request = $request;
        $this->wait = $wait;

        $this->storeLocal(self::HANDLE, $handle);
        $this->storeLocal(self::FAIL, $fail);
        $this->storeLocal(self::FINALLY, $finally);
    }

    public function onRun(): void {
        if ($this->wait !== null)
            sleep($this->wait);

        $error = null;
        $result = $this->request->execute($error);

        if($error !== null) {
            $this->setResult([
                "error" => $error
            ]);
            return;
        }

        $this->setResult([
            "data" => $result
        ]);
    }

    public function onCompletion(): void {
        $result = $this->getResult();

        $handle = null;
        try {
            $handle = $this->fetchLocal(self::HANDLE);
        } catch(\Exception $e) {
        }

        $fail = null;
        try {
            $fail = $this->fetchLocal(self::FAIL);
        } catch(\Exception $e) {
        }

        $finally = null;
        try {
            $finally = $this->fetchLocal(self::FINALLY);
        } catch(\Exception $e) {
        }

        $request = $this->request;

        if(isset($result["error"])) {
            if($fail !== null) {
                ($fail)($result["error"], $request);
            }

            $retryTimes = $request->getRetryTimes();
            $retryBlockTimes = $request->getRetryBlockTimes();
            if($retryTimes !== null && ($retryTimes === -1 || $retryTimes > 0)) {
                Server::getInstance()->getLogger()->debug("Retrying request in async task " . $request);
                if($retryTimes !== -1) {
                    $request->decreaseRetryTime(1);
                }

                if($retryBlockTimes === 0) {
                    $request->async();
                } else {
                    Server::getInstance()->getLogger()->debug("Will run request in async task " . $request . " in " . $retryBlockTimes . " seconds");
                    Server::getInstance()->getAsyncPool()->submitTask(new RequestTask($request, $handle, $fail, $finally, $retryBlockTimes));
                }
                return;
            }

            if($finally !== null) {
                ($finally)();
            }
            return;
        }

        if($handle !== null) {
            $response = new Response($result["data"]);
            ($handle)($response);
        }

        if($finally !== null) {
            ($finally)();
        }
    }
}