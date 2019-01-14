<?php

namespace Viloveul\Container\Contracts;

use Psr\Container\ContainerInterface as ContainerInterface;

interface Container extends ContainerInterface
{
    /**
     * @param string $class
     */
    public function factory(string $class);

    public static function getInstance(): self;

    /**
     * @param $callback
     */
    public function invoke(callable $callback);

    /**
     * @param $id
     * @param $target
     * @param array     $params
     */
    public function map($id, $target, array $params = []);

    public function raw($id);

    /**
     * @param $id
     * @param $target
     * @param array     $params
     */
    public function remap($id, $target, array $params = []);

    /**
     * @param self $instance
     */
    public static function setInstance(self $instance);
}
