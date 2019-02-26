<?php

namespace ViloveulContainerSample;

use ViloveulContainerSample\BazInterface;
use ViloveulContainerSample\FooInterface;

class Baz implements BazInterface
{
    /**
     * @var string
     */
    public $foo;

    /**
     * @param FooInterface $foo
     */
    public function __construct(FooInterface $foo)
    {
        $this->foo = $foo;
    }
}
