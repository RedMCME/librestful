<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\scheduler\AsyncTask;
use redmc\librestful\request\Request;

final class RequestTask extends AsyncTask {
    public const METHOD_GET = 0;
    public const METHOD_POST = 1;

    private Request $request;
    private ?\Closure $handle;
    private ?\Closure $onFail;

    public function __construct(Request $request, ?\Closure $handle, ?\Closure $onFail) {
        $this->request = $request;
        $this->handle = $handle;
        $this->onFail = $onFail;
    }

    public function onRun(): void {
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

        if(isset($result["error"])) {
            if($this->onFail !== null) {
                ($this->onFail)($result["error"]);
            }
        } else {
            if($this->handle !== null) {
                $response = new Response($result["data"], $this->request->getPlayers(), $this->request->getWorlds());
                if (
                    ($this->request->willAbortIfNoPlayer() && count($response->onlinePlayers()) === 0)
                    ||
                    ($this->request->willAbortIfNoWorld() && count($response->loadedWorlds()) === 0)
                ) return;
                ($this->handle)($response);
            }
        }
    }
}