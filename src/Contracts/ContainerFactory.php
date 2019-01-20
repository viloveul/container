<?php

namespace Viloveul\Container\Contracts;

use Viloveul\Container\Contracts\Container;

interface ContainerFactory
{
    /**
     * @param array $definitions
     */
    public static function instance(array $definitions = []): Container;
}
