<?php

namespace Viloveul\Container;

use Viloveul\Container\Contracts\Container as IContainer;

trait ContainerAwareTrait
{
    /**
     * @var mixed
     */
    protected $container = null;

    /**
     * @return mixed
     */
    public function getContainer(): IContainer
    {
        return $this->container;
    }

    /**
     * @param IContainer $container
     */
    public function setContainer(IContainer $container)
    {
        $this->container = $container;
    }
}
