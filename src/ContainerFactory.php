<?php

namespace Viloveul\Container;

use Viloveul\Container\Container;
use Viloveul\Container\Contracts\Container as IContainer;
use Viloveul\Container\Contracts\ContainerFactory as IContainerFactory;

class ContainerFactory implements IContainerFactory
{
    /**
     * @param  array   $definitions
     * @return mixed
     */
    public static function instance(array $definitions = []): IContainer
    {
        $container = Container::getInstance();
        foreach ($definitions as $key => $value) {
            $container->set($key, $value);
        }
        return $container;
    }
}
