<?php

namespace Viloveul\Container;

use Viloveul\Container\Container as ContainerClass;
use Viloveul\Container\Contracts\Container as IContainer;

trait ContainerInjectorTrait
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
        if ($this->container instanceof IContainer) {
            return $this->container;
        } else {
            $this->container = ContainerClass::getInstance();
            return $this->container;
        }
    }

    /**
     * @param IContainer $container
     */
    public function setContainer(IContainer $container)
    {
        $this->container = $container;
    }
}
