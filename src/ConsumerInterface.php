<?php

namespace server;

interface ConsumerInterface
{
    public function consume();

    /**
     * 获取消费者监听的端口
     * @return int
     */
    public function listenPort(): int;

    /**
     * @return int
     */
    public function getWorkerNumber(): int;

    /**
     * @return int
     */
    public function getTaskWorkerNumber(): int;

    /**
     * @return bool
     */
    public function getDaemonize(): bool;

    /**
     * @return integer
     */
    public function getBackLog(): int;

    /**
     * @return string
     */
    public function getLogFilePath(): string;
}

