<?php

namespace server\Consumers;


class TestConsumer extends ConsumerBase
{

    //OnStart是在Master进程的Master线程中执行的
    public function onStart($serv)
    {
        echo sprintf("服务器Master进程ID是:%s,Manager进程ID是:%s\n", $serv->master_pid, $serv->manager_pid);
        savePidInfo($serv);
    }

    //监听连接进入事件
    public function onConnect($serv, $fd)
    {
        echo "Client: Connect.\n";
    }

    //监听数据接收事件
    public function onReceive($serv, $fd, $from_id, $data)
    {
        //这一个时间监听是在 refactor 进程中处理的，下面打印一下
        printPid();

        //向 worker 投放请求数据，这里其实就是类似nginx-->fpm进行转发的操作.
        $task_id = $serv->task($data);

        echo sprintf("Dispath AsyncTask: id=%s, sending data is: %s\n", $task_id, json_encode($data));
        $serv->send($fd, "Server: " . $data);
        //关闭指定客户端的链接，如果不调用这个方法，会导致这一条 Tcp 链接保持链接。
        $serv->close($fd);
    }

    //处理异步任务
    public function onTask($serv, $task_id, $from_id, $data)
    {
        //这一个事件监听是在 worker 进程中处理的，下面打印一下
        printPid();
        echo "开始执行异步任务:{$task_id}\n";
        $i = 0;
        while ($i++ < 10) {
            echo "\t正在处理异步任务:{$task_id}-{$i}\n";
        }
        //返回任务执行的结果
        $serv->finish("$data");
    }

    //处理异步任务的结果
    public function onFinish($serv, $task_id, $data)
    {
        echo "AsyncTask[$task_id] Finish: $data" . PHP_EOL;
    }

    public function onClose()
    {
        echo "链接关闭\n";
    }
}


