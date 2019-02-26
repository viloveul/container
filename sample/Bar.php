<?php

namespace ViloveulContainerSample;

use ViloveulContainerSample\BarInterface;
use ViloveulContainerSample\Foo;

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
