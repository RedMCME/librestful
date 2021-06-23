<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

use InvalidArgumentException;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Response;

class DefaultRequestThread extends Thread implements RequestThread {
    private SleeperNotifier $notifier;

    private static int $nextSlaveNumber = 0;

    protected int $slaveNumber;
    protected RequestSendQueue $bufferSend;
    protected RequestRecvQueue $bufferRecv;

    protected bool $busy = false;

    protected $classLoaders;

    public function __construct(
        SleeperNotifier $notifier,
        RequestSendQueue $bufferSend = null,
        RequestRecvQueue $bufferRecv = null
    ) {
        $this->notifier = $notifier;

        $this->slaveNumber = self::$nextSlaveNumber++;
        $this->bufferSend = $bufferSend ?? new RequestSendQueue();
        $this->bufferRecv = $bufferRecv ?? new RequestRecvQueue();
        $this->setClassLoader(Server::getInstance()->getPluginManager()->getPlugin("DEVirion")->getVirionClassLoader());

        $this->start(PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS);
    }

    public function onRun(): void {
        while (true) {
            $row = $this->bufferSend->fetchQuery();
            if ($row === null) {
                continue;
            }

            $this->busy = true;
            [$requestId, $execute, $executeParams] = $row;

            try {
                $start = microtime(true);
                $result = $execute(...$executeParams);
                $this->bufferRecv->publishResult(
                    $requestId,
                    new Response($result, microtime(true) - $start)
                );
            } catch (RequestErrorException $error) {
                $this->bufferRecv->publishError($requestId, $error);
            }

            $this->notifier->wakeupSleeper();
            $this->busy = false;
        }
    }

    public function isBusy(): bool {
        return $this->busy;
    }

    public function stopRunning(): void {
        $this->bufferSend->invalidate();
        parent::quit();
    }

    public function quit(): void {
        $this->stopRunning();
    }

    public function addRequest(int $requestId, callable $execute, array $executeParams): void {
        $this->bufferSend->scheduleQuery($requestId, $execute, $executeParams);
    }

    public function readResults(array &$callbacks): void {
        while ($this->bufferRecv->fetchResult($requestId, $result)) {
            if (!isset($callbacks[$requestId])) {
                throw new InvalidArgumentException(
                    "Missing handler for request #$requestId"
                );
            }

            $callback = $callbacks[$requestId];
            unset($callbacks[$requestId]);
            $callback($result);
        }
    }

    public function getSlaveNumber(): int {
        return $this->slaveNumber;
    }
}
