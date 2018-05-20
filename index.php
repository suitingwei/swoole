<?php

require_once __DIR__ . '/vendor/autoload.php';


(new \server\Kernel\Application(realpath(__DIR__)))->run();
