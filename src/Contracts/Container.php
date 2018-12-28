<?php

namespace Viloveul\Container\Contracts;

use Psr\Container\ContainerInterface as ContainerInterface;

interface Container extends ContainerInterface
{
    /**
     * @param string $class
     */
    public function factory(string $class);

    public static function getInstance(): Container;

    /**
     * @param $callback
     */
    public function invoke(callable $callback);

    /**
     * @param self $instance
     */
    public static function setInstance(Container $instance);
}
