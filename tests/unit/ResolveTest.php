<?php

class ResolveTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected $myContainer;
    
    protected function _before()
    {
        $this->myContainer = \Viloveul\Container\ContainerFactory::instance();
    }

    protected function _after()
    {
    }

    public function testWithClass()
    {
        $this->myContainer->set(
            ViloveulContainerSample\Foo::class,
            ViloveulContainerSample\Foo::class
        );
        $this->assertInstanceOf(
            ViloveulContainerSample\Foo::class,
            $this->myContainer->make(ViloveulContainerSample\Bar::class)->foo
        );
    }

    public function testWithInterface()
    {
        $this->myContainer->set(
            ViloveulContainerSample\FooInterface::class,
            ViloveulContainerSample\Foo::class
        );
        $this->assertInstanceOf(
            ViloveulContainerSample\FooInterface::class,
            $this->myContainer->make(ViloveulContainerSample\Baz::class)->foo
        );
    }
}
