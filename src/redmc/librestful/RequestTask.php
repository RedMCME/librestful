<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\scheduler\AsyncTask;
use redmc\librestful\request\Request;

final class RequestTask extends AsyncTask {

    private Request $request;

    public const HANDLE = "handle";
    public const FAIL = "fail";

    public function __construct(Request $request, ?\Closure $handle, ?\Closure $onFail) {
        $this->request = $request;
        $this->storeLocal(self::HANDLE, $handle);
        $this->storeLocal(self::FAIL, $onFail);
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

        $handle = null;
        try {
            $handle = $this->fetchLocal(self::HANDLE);
        } catch(\Exception $e){}

        $fail = null;
        try {
            $fail = $this->fetchLocal(self::FAIL);
        } catch(\Exception $e){}

        if(isset($result["error"])) {
            if($fail !== null) {
                ($fail)($result["error"]);
            }
            return;
        }

        if($handle !== null) {
            $response = new Response($result["data"]);
            ($handle)($response);
        }
    }
}