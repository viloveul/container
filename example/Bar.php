<?php

namespace ViloveulContainerExample;

use ViloveulContainerExample\BarInterface;
use ViloveulContainerExample\Foo;

class Bar implements BarInterface
{
    /**
     * @var string
     */
    public $foo;

    /**
     * @param Foo $foo
     */
    public function __construct(Foo $foo)
    {
        $this->foo = $foo;
    }
}
