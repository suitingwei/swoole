<?php
$server = new swoole_server("127.0.0.1", 9502);

//设置异步任务的工作进程数量
$server->set([
	//设置 worker进程的数目，这个类似lnmp 中的fpm, Refactor 进程接受了请求之后直接转发给worker进程处理，worker处理完之后再扔回给refactor
	'worker_num' => 4,
	'task_worker_num' => 4,
 	'daemonize' => true,
    'backlog' => 128,
	//指定 Log 文件，因为程序是在真正的守护进程中执行的，所以任何echo 都不会打印到屏幕
	'log_file' => '/home/suitw/code/swoole.log'
]);


//OnStart是在Master进程的Master线程中执行的
$server->on('start',function($serv){
	echo sprintf("服务器Master进程ID是:%s,Manager进程ID是:%s\n",$serv->master_pid,$serv->manager_pid);
	savePidInfo($serv);
});

//监听连接进入事件
$server->on('connect', function ($serv, $fd) {  
    echo "Client: Connect.\n";
});

//监听数据接收事件
$server->on('receive', function ($serv, $fd, $from_id, $data) {
	//这一个时间监听是在 refactor 进程中处理的，下面打印一下
	printPid();

	//向 worker 投放请求数据，这里其实就是类似nginx-->fpm进行转发的操作.
    $task_id = $serv->task($data);

    echo sprintf("Dispath AsyncTask: id=%s, sending data is: %s\n",$task_id,json_encode($data));
    $serv->send($fd, "Server: ".$data);
	//关闭指定客户端的链接，如果不调用这个方法，会导致这一条 Tcp 链接保持链接。
	$serv->close($fd);
});

//处理异步任务
$server->on('task', function ($serv, $task_id, $from_id, $data) {
	//这一个事件监听是在 worker 进程中处理的，下面打印一下
	printPid();

    echo "开始执行异步任务:{$task_id}\n";
    $i=0;
    while($i++ < 10){
		echo "\t正在处理异步任务:{$task_id}-{$i}\n";
    }
    //返回任务执行的结果
    $serv->finish("$data");
});


//处理异步任务的结果
$server->on('finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});

$server->on('close',function(){
	echo "链接关闭\n";
});


/**
 * 打印进程的 id
 * @return void
 **/
function printPid(){
	echo sprintf("\n\e[35m当前进程 ID 是:%s\e[0m\n",posix_getpid());	
}

/**
 * 保存服务器的主进程 id，用于重启 
 * @return void
 **/
function savePidInfo($serv){
	echo sprintf("保存Master进程ID:%s 到文件:%s.\n",$serv->master_pid,__DIR__.'/pid.info');
	file_put_contents(__DIR__.'/pid.info',$serv->master_pid);

	return true;
}


/**
 * 获取服务器 master 进程 id，用于重启，其实这里用不到。重启的操作应该是在整个 swoole 外部。
 * 由运维主动执行脚本触发，能用的只是保存pid到文件而已
 * @depreted
 * @return void
 **/
function getPidFromFile(){
	return file_get_contents('pid.info');
}

//启动服务器
$server->start(); 
