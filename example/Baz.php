<?php

namespace ViloveulContainerExample;

use ViloveulContainerExample\BazInterface;
use ViloveulContainerExample\FooInterface;

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
