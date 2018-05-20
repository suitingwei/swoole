<?php

namespace server\Consumers;

use server\Kernel\Application;

abstract class ConsumerBase
{
    protected $events = [
        'start'   => 'onStart',
        'connect' => 'onConnect',
        'receive' => 'onReceive',
        'task'    => 'onTask',
        'finish'  => 'onFinish',
        'close'   => 'onClose',
    ];

    /**
     * @var
     */
    protected $server;

    /**
     * Consumer constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->initServer();

        $this->initServerConfigure();

        $this->initServerEvents();
    }

    /**
     *  Init the swoole server.
     */
    private function initServer()
    {
        $this->server = new \Swoole\Server("127.0.0.1", $this->listenPort());
    }

    private function initServerConfigure()
    {
        //设置异步任务的工作进程数量
        $this->server->set([
            //设置 worker进程的数目，这个类似lnmp 中的fpm, Refactor 进程接受了请求之后直接转发给worker进程处理，worker处理完之后再扔回给refactor
            'worker_num'      => $this->getWorkerNumber(),
            //任务 worker 进程的数目，可以理解为lnmp中的redis异步队列消费者数目，worker 中的耗时操作可以扔给 task worker 执行,这个不会导致 worker 进程阻塞
            'task_worker_num' => $this->getTaskWorkerNumber(),
            //是否设置为守护进程运行模式
            'daemonize'       => $this->getDaemonize(),
            'backlog'         => $this->getBackLog(),
            //指定 Log 文件，因为程序是在真正的守护进程中执行的，所以任何echo 都不会打印到屏幕
            'log_file'        => $this->getLogFilePath(),
        ]);

    }

    /**
     * 初始化 server 的监听事件回调。
     * 默认的事件回调被映射为 event ==> onEvent
     * @throws \Exception
     */
    private function initServerEvents()
    {
        foreach ($this->events as $swooleEventName => $consumerCallback) {
            if (!method_exists($this, $consumerCallback)) {
                throw new \Exception(sprintf("Swoole 事件:%s,没有设置回调:%s", $swooleEventName, $consumerCallback));
            }
            $this->server->on($swooleEventName, [$this, $consumerCallback]);
        }
    }

    /**
     *
     */
    public function consume()
    {
        echo sprintf("开始执行消费操作\n");
        //启动 Server
        $this->server->start();
    }

    /**
     * Syntax sugar.
     * @throws \Exception
     */
    public static function run()
    {
        (new static)->consume();
    }

    /**
     * 获取消费者监听的端口
     * @return int
     */
    public function listenPort(): int
    {
        return 9502;
    }

    /**
     * @return int
     */
    public function getWorkerNumber(): int
    {
        return 4;
    }

    /**
     * @return int
     */
    public function getTaskWorkerNumber(): int
    {
        return 4;
    }

    /**
     * @return bool
     */
    public function getDaemonize(): bool
    {
        return true;
    }

    /**
     * @return integer
     */
    public function getBackLog(): int
    {
        return true;
    }

    public function getClassShortName()
    {
        return substr(static::class, strrpos(static::class, '\\')+1);
    }

    /**
     * @return string
     */
    public function getLogFilePath(): string
    {
        return Application::getInstance()->getLogPath() . '/' . $this->getClassShortName() . '.log';
    }
}

