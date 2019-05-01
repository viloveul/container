<?php

namespace Viloveul\Container\Contracts;

use Psr\Container\ContainerInterface as ContainerInterface;

interface Container extends ContainerInterface
{
    /**
     * @param $id
     */
    public function __get($id);

    /**
     * @param string $id
     * @param string $target
     */
    public function alias(string $id, string $target): void;

    /**
     * @param string $id
     * @param bool   $recursive
     */
    public function forget(string $id, bool $recursive): void;

    /**
     * @param string $name
     */
    public function getRealIdentifier(string $name): string;

    /**
     * @param $callback
     * @param array       $params
     */
    public function invoke(callable $callback, array $params);

    /**
     * @param string $class
     * @param array  $params
     */
    public function make(string $class, array $params);

    /**
     * @param $id
     * @param $target
     * @param array     $params
     */
    public function map($id, $target, array $params): void;

    /**
     * @param $id
     */
    public function raw($id);

    /**
     * @param $id
     * @param $target
     * @param array     $params
     */
    public function remap($id, $target, array $params): void;
}
