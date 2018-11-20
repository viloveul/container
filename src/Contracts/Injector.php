<?php

namespace Viloveul\Container\Contracts;

use Viloveul\Container\Contracts\Container;

interface Injector
{
    public function getContainer(): Container;

    /**
     * @param Container $container
     */
    public function setContainer(Container $container);
}
