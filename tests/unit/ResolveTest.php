<?php 

use ViloveulContainerExample;

class ResolveTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testWithClass()
    {
        $container = \Viloveul\Container\ContainerFactory::instance();
        $container->set(
            ViloveulContainerExample\Foo::class,
            ViloveulContainerExample\Foo::class
        );
        $this->assertInstanceOf(
            ViloveulContainerExample\Foo::class,
            $container->make(ViloveulContainerExample\Bar::class)->foo
        );
    }

    public function testWithInterface()
    {
        $container = \Viloveul\Container\ContainerFactory::instance();
        $container->set(
            ViloveulContainerExample\FooInterface::class,
            ViloveulContainerExample\Foo::class
        );
        $this->assertInstanceOf(
            ViloveulContainerExample\FooInterface::class,
            $container->make(ViloveulContainerExample\Baz::class)->foo
        );
    }
}
