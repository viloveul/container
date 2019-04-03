<?php

namespace Viloveul\Container;

use Viloveul\Container\Container;
use Viloveul\Container\Contracts\Container as IContainer;

class ContainerFactory
{
    /**
     * @var mixed
     */
    protected static $container;

    /**
     * @param  array   $definitions
     * @return mixed
     */
    public static function instance(array $definitions = []): IContainer
    {
        if (!(static::$container instanceof IContainer)) {
            static::$container = new Container();
        }
        foreach ($definitions as $key => $value) {
            static::$container->set($key, $value);
        }
        return static::$container;
    }
}
