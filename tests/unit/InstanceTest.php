<?php 

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

    // tests
    public function testNewInstance()
    {
        $this->assertInstanceOf(
            \Viloveul\Container\Contracts\Container::class,
            \Viloveul\Container\ContainerFactory::instance()
        );
    }

    public function testResolveWithStringClass()
    {
        $container = \Viloveul\Container\ContainerFactory::instance();
        $container->set('dor', \stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $container->get('dor'));
    }

    public function testResolveWithClosure()
    {
        $container = \Viloveul\Container\ContainerFactory::instance();
        $container->set('hello', function() {
            return new \stdClass;
        });
        $this->assertInstanceOf(\stdClass::class, $container->get('hello'));
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
}
