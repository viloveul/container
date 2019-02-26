<?php 

use ViloveulContainerSample;

class InstanceTest extends \Codeception\Test\Unit
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

    public function testInstanced()
    {
        $this->assertInstanceOf(
            \Viloveul\Container\Contracts\Container::class,
            $this->myContainer
        );
    }

    public function testMakeFromString()
    {
        $this->myContainer->set('foo', ViloveulContainerSample\Foo::class);
        $this->assertInstanceOf(ViloveulContainerSample\Foo::class, $this->myContainer->get('foo'));
    }

    public function testMakeFromClosure()
    {
        $this->myContainer->set('fooClosure', function() {
            return new ViloveulContainerSample\Foo();
        });
        $this->assertInstanceOf(ViloveulContainerSample\Foo::class, $this->myContainer->get('fooClosure'));
    }

    public function testInvokeClosure()
    {
        $key = 'dor';
        $invoker = function($abc) {
            return $abc;
        };
        $this->assertEquals($key, $this->myContainer->invoke($invoker, ['abc' => $key]));
    }

    public function testInjectContainerAware()
    {
        $mine = $this->myContainer->make(ViloveulContainerSample\Injector::class);
        $this->assertInstanceOf(
            \Viloveul\Container\Contracts\Container::class,
            $mine->getContainer()
        );
    }
}
