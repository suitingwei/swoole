<?php

namespace server\Kernel;

class Application
{
    /**
     * 保存应用的实例.
     * 在整个应用已经运行之后，如果想要从 application 中获取一些信息，
     * 那么必须能够随时方便的获取到application实例，可以写静态方法，
     * 也可以写一个全局函数，相对来说写静态方法更规范一些。
     * @var Application
     */
    private static $instance = null;

    /**
     * 记录整个项目的根目录。
     * 因为一个大型框架想要保持灵活性的话，一定是要把某些模块做成可配置的，比如数据库，比如缓存，
     * 甚至是框架的部分模块，比如 laravel 的 serviceProvider, yii 的 module，这些想要做成可配置，
     * 那么就必须有一个 config 文件去记录对应的文件。 那么相应的如何查找这个文件就成了一个问题，
     * 为了简化目录切换操作，框架都会规定好某些目录是做什么用的，然后在整个框架启动的时候就把
     * 根目录保存在 Application 实例中，这样以后运行时，永远可以实时获取这些基础信息。
     * 这也就解释了Application的基础功能，就是文件操作、配置操作.
     * @var string
     */
    private $basePath = null;

    /**
     * 记录框架配置的消费者list.
     * 消费者都是配置在config/consumers.php中的，而当加载到 application 中之后，最好是做一次
     * 内存缓存，这样不用每一次都require了.
     * @var array
     */
    private static $consumers = null;

    /**
     * 获取 log 文件目录
     * @return string
     */
    public function getLogPath()
    {
        return $this->getBasePath() . '/logs';
    }

    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;

        self::$instance = $this;
    }

    public function getBasePath()
    {
        echo sprintf("项目的根目录是:%s", $this->basePath);
        return $this->basePath;
    }

    /**
     * 获取所有的消费者
     * @return array
     */
    public function getConsumers()
    {
        if (is_null(self::$consumers)) {
            return self::$consumers = require $this->basePath . '/config/consumers.php';
        }
        return self::$consumers;
    }

    /**
     * @return Application
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function run()
    {
        $consumers = $this->getConsumers();

        #获取输入参数,这样可以指定运行某一个消费者
        $params = getopt('h::', ['consumer::']);

        $selectedConsumer = $params['consumer'];

        if (empty($selectedConsumer) || !isset($consumers[$selectedConsumer])) {
            die(sprintf("所选择的消费者:%s不存在", $selectedConsumer));
        }

        /**
         * @var \server\ConsumerBase
         */
        $consumerServer = new $consumers[$selectedConsumer];

        $consumerServer->consume();

    }
}
