<?php
$httpServer = new swoole_http_server("127.0.0.1", 9501);


$httpServer->on("start", function ($server) {
    echo "Swoole http server is started at http://127.0.0.1:9501\n";
});

$httpServer->on("request", function ($request, $response) {
	$mode = $request->get['mode'];
    $response->header("Content-Type", "text/plain");
    $response->header("Should I fuck you ", "yes");
    $response->end("<h1>Hello World</h1>");
});

//swoole 不支持 httpserver 去做 worker 操作
#$httpServer->on('onWorkerStart',function(){
#	echo "OnWorkerStart Event Triggered..";
#});

//swoole 不支持 httpserver 去做 worker 操作
#$httpServer->on('onTask',function(){
#	echo "onTask Event Triggered..";
#});

//监听客户端关闭连接的事件
//注意这里不是说服务器关闭
$httpServer->on('close',function($server,$fd){
    echo "Client is closing connection...\n";
});

$httpServer->start(); 
