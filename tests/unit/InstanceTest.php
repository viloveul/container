<?php 

use ViloveulContainerExample;

class InstanceTest extends \Codeception\Test\Unit
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

    public function testNormalInstance()
    {
        $this->assertInstanceOf(
            \Viloveul\Container\Contracts\Container::class,
            new \Viloveul\Container\Container()
        );
    }

    public function testWithFactory()
    {
        $this->assertInstanceOf(
            \Viloveul\Container\Contracts\Container::class,
            \Viloveul\Container\ContainerFactory::instance()
        );
    }

    public function testMakeFromString()
    {
        $container = \Viloveul\Container\ContainerFactory::instance();
        $container->set('foo', ViloveulContainerExample\Foo::class);
        $this->assertInstanceOf(ViloveulContainerExample\Foo::class, $container->get('foo'));
    }

    public function testMakeFromClosure()
    {
        $container = \Viloveul\Container\ContainerFactory::instance();
        $container->set('fooClosure', function() {
            return new ViloveulContainerExample\Foo();
        });
        $this->assertInstanceOf(ViloveulContainerExample\Foo::class, $container->get('fooClosure'));
    }

    public function testInvokeClosure()
    {
        $key = 'dor';
        $container = \Viloveul\Container\ContainerFactory::instance();
        $invoker = function($abc) {
            return $abc;
        };
        $this->assertEquals($key, $container->invoke($invoker, ['abc' => $key]));
    }

    public function testInjectContainerAware()
    {
        $container = \Viloveul\Container\ContainerFactory::instance();
        $mine = $container->make(ViloveulContainerExample\Injector::class);
        $this->assertInstanceOf(
            \Viloveul\Container\Contracts\Container::class,
            $mine->getContainer()
        );
    }
}
