<?php

namespace Amp\Parallel\Worker;

use Yii;

/**
 * Task implementation dispatching a simple callable.
 */
final class CallableTask implements Task
{
    /** @var string */
    private $callable;

    /** @var mixed[] */
    private $args;

    /**
     * @param callable $callable Callable will be serialized.
     * @param mixed    $args Arguments to pass to the function. Must be serializable.
     */
    public function __construct(callable $callable, array $args)
    {
        $this->callable = $callable;
        $this->args = $args;
    }

    public function run(Environment $environment)
    {
        if ($this->callable instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance as a callable, the class must be autoloadable');
        }

        if (\is_array($this->callable) && ($this->callable[0] ?? null) instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance method as a callable, the class must be autoloadable');
        }

        (function () {
            require_once(__DIR__ . '/../../../../autoload.php');
            require_once(__DIR__ . '/../../../../yiisoft/yii2/Yii.php');
            require_once(__DIR__ . '/../../../../smarty/smarty/libs/Smarty.class.php');

            $config = require __DIR__ . '/../../../../../config/console.php';

            $application = new \yii\console\Application($config);

            $renderer = $application->getView()->renderers['tpl'];

            if(is_array($renderer)) {
                Yii::$app->getView()->renderers['tpl'] = $renderer = Yii::createObject($renderer);
            }

            $renderer->getSmarty()->addPluginsDir(dirname(__DIR__) . '/extensions/smarty/plugins');
        })();

        return ($this->callable)(...$this->args);
    }
}
