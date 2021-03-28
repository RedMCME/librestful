<?php

declare(strict_types=1);

namespace redmc\librestful;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;

final class QueryTask extends AsyncTask {
    public const METHOD_GET = 0;
    public const METHOD_POST = 1;

    private string $url;
    private int $timeout;
    private $headers;
    private ?\Closure $handle;
    private ?\Closure $onFail;
    private int $method;
    private $args;

    /**
     * QueryTask constructor.
     * @param int $method
     * @param string $url
     * @param int $timeout
     * @param array $headers
     * @param array $args
     * @param \Closure|null $handle
     * @param \Closure|null $onFail
     */
    public function __construct(int $method, string $url, int $timeout, array $headers, array $args, ?\Closure $handle = null, ?\Closure $onFail = null) {
        $this->url = $url;
        $this->timeout = $timeout;
        $this->headers = serialize($headers);
        $this->handle = $handle;
        $this->onFail = $onFail;
        $this->method = $method;
        $this->args = serialize($args);
    }

    public function onRun(): void {
        $headers = (array) unserialize($this->headers);
        $err = null;
        $result = null;

        if ($this->method === self::METHOD_GET) {
            $result = Internet::getURL($this->url, $this->timeout, $headers, $err);
        } elseif ($this->method === self::METHOD_POST) {
            $args = (array) unserialize($this->args);
            $result = Internet::postURL($this->url, $args, $this->timeout, $headers, $err);
        }

        if ($err !== null) {
            $this->setResult([
                "error" => $err
            ]);
            return;
        }

        $this->setResult([
            "data" => $result
        ]);
    }

    public function onCompletion(): void {
        $result = $this->getResult();

        if (isset($result["error"])) {
            if($this->onFail !== null) {
                ($this->onFail)($result["error"]);
            }
        } else {
            if ($this->handle !== null) {
                ($this->handle)($result["data"]);
            }
        }
    }
}