<?php

/**
 * 这里记录了所有可用的消费者，key 值是使用脚本启动的时候的选项值
 * E.G. php index.php --consumer = test,则会启用对应的配置
 */
return [
    'test' => \server\Consumers\TestConsumer::class
];

