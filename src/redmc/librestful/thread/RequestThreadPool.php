<?php

declare(strict_types=1);

namespace redmc\librestful\thread;

use InvalidArgumentException;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;

class RequestThreadPool implements RequestThread {
    private SleeperNotifier $notifier;

    /** @var DefaultRequestThread[] */
    private array $workers = [];

    private int $workerLimit;

    private RequestSendQueue $bufferSend;
    private RequestRecvQueue $bufferRecv;

    private ?ConnectorLayer $connectorLayer = null;

    public function setConnectorLayer(ConnectorLayer $connectorLayer): void {
        $this->connectorLayer = $connectorLayer;
    }

    public function __construct(int $workerLimit) {
        $this->notifier = new SleeperNotifier();
        Server::getInstance()
            ->getTickSleeper()
            ->addNotifier($this->notifier, function (): void {
                assert($this->connectorLayer instanceof ConnectorLayer);
                $this->connectorLayer->checkResults();
            });

        $this->workerLimit = $workerLimit;
        $this->bufferSend = new RequestSendQueue();
        $this->bufferRecv = new RequestRecvQueue();

        $this->addWorker();
    }

    private function addWorker(): void {
        $this->workers[] = new DefaultRequestThread(
            $this->notifier,
            $this->bufferSend,
            $this->bufferRecv
        );
    }

    public function join(): void {
        var_dump("eren");
        foreach ($this->workers as $worker) {
            var_dump("s#" . $worker->getSlaveNumber() . " > join");
            $worker->join();
        }
    }

    public function stopRunning(): void {
        var_dump("ahmed");
        foreach ($this->workers as $worker) {
            var_dump("s#" . $worker->getSlaveNumber() . " > stop");
            $worker->stopRunning();
        }
    }

    public function addRequest(int $requestId, callable $execute, array $executeParams): void {
        $this->bufferSend->scheduleQuery($requestId, $execute, $executeParams);

        // check if we need to increase worker size
        foreach ($this->workers as $worker) {
            if (!$worker->isBusy()) {
                return;
            }
        }

        if (count($this->workers) < $this->workerLimit) {
            $this->addWorker();
        }
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

    public function getLoad(): float {
        return $this->bufferSend->count() / (float) $this->workerLimit;
    }
}
