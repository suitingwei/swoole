<?php
/**
 * Created by PhpStorm.
 * User: sui
 * Date: 2018/5/20
 * Time: 15:10
 */

if(!function_exists('app')){
    function app () {
        return \server\Kernel\Application::getInstance();
    }
}