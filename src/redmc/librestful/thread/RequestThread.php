<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

interface RequestThread {
    /**
     * Joins the thread
     *
     * @see https://php.net/thread.join Thread::join
     */
    public function join();

    /**
     * Signals the thread to stop waiting for requests when the send buffer is cleared.
     */
    public function stopRunning(): void;

    public function addRequest(int $requestId, callable $execute, array $executeParams): void;

    /**
     * Handles the results that this request has completed
     *
     * @param callable[] $callbacks
     */
    public function readResults(array &$callbacks): void;
}
