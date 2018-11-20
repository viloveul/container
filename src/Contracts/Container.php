<?php

namespace Viloveul\Container\Contracts;

use Psr\Container\ContainerInterface as ContainerInterface;

interface Container extends ContainerInterface
{
    /**
     * @param $class
     */
    public function factory($class);

    /**
     * @param $abstract
     */
    public function get($abstract);

    public static function getInstance(): Container;

    /**
     * @param $callback
     */
    public function invoke(callable $callback);

    /**
     * @param $abstract
     * @param $class
     */
    public function set($abstract, $class);

    /**
     * @param self $instance
     */
    public static function setInstance(Container $instance);
}
