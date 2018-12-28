<?php

namespace Viloveul\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Viloveul\Container\ContainerException;
use Viloveul\Container\Contracts\Container as IContainer;
use Viloveul\Container\Contracts\Injector as IContainerInjector;

class Container implements IContainer
{
    /**
     * @var array
     */
    protected $components = [];

    /**
     * @var mixed
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @param array $components
     */
    public function __construct(array $components = [])
    {
        static::setInstance($this);

        if (count($components) > 0) {
            foreach ($components as $key => $param) {
                if (!$this->has($key)) {
                    $this->set($key, $param);
                }
            }
        }
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * @param  string  $class
     * @param  array   $params
     * @return mixed
     */
    public function factory(string $class, array $params = [])
    {
        try {
            $reflection = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();

            if (null === $constructor || $constructor->getNumberOfParameters() === 0) {
                $object = $reflection->newInstance();
            } else {
                $object = $reflection->newInstanceArgs($this->resolve($constructor, $params));
            }

            if ($object instanceof IContainerInjector) {
                $object->setContainer($this);
            }

            return $object;

        } catch (ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * @param $id
     */
    public function get($id)
    {
        if (!array_key_exists($id, $this->instances)) {
            if ($this->has($id)) {
                if (is_callable($this->components[$id])) {
                    $this->instances[$id] = $this->invoke($this->components[$id]);
                } elseif (is_array($this->components[$id])) {
                    $params = $this->components[$id];
                    $class = $params['class'];
                    unset($params['class']);
                    $this->instances[$id] = $this->factory($class, $params);
                } else {
                    $this->instances[$id] = $this->factory($this->components[$id]);
                }
            } else {
                throw new NotFoundException("{$id} does not found.");
            }
        }
        return $this->instances[$id];
    }

    public static function getInstance(): IContainer
    {
        if (!(static::$instance instanceof IContainer)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * @param $id
     */
    public function has($id)
    {
        return array_key_exists($id, $this->components);
    }

    /**
     * @param  $function
     * @param  array       $params
     * @return mixed
     */
    public function invoke(callable $function, array $params = [])
    {
        $invokerArguments = [];
        try {
            if (is_array($function)) {
                $reflection = new ReflectionMethod($function[0], $function[1]);
                $invokerArguments[] = $function[0];
            } else {
                $reflection = new ReflectionFunction($function);
            }
            if (0 === $reflection->getNumberOfParameters()) {
                return call_user_func_array([$reflection, 'invoke'], $invokerArguments);
            }
            $invokerArguments[] = $this->resolve($reflection, $params);
            return call_user_func_array([$reflection, 'invokeArgs'], $invokerArguments);
        } catch (ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * @param $id
     * @param $param
     */
    public function set($id, $param)
    {
        if (!$this->has($id) && $id !== Closure::class) {
            if (is_string($param)) {
                if (!class_exists($param)) {
                    throw new ContainerException("param \$param must be exists.");
                } else {
                    $this->components[$id] = $param;
                }
            } elseif (is_callable($param)) {
                $this->components[$id] = $param;
            } elseif (is_array($param)) {
                if (!array_key_exists('class', $param) || !class_exists($param['class'])) {
                    throw new ContainerException("param \$param must has index class and exists.");
                } else {
                    $this->components[$id] = $param;
                }
            } else {
                throw new ContainerException("param \$param must be string|callable|array.");
            }
        }
    }

    /**
     * @param IContainer $container
     */
    public static function setInstance(IContainer $container)
    {
        static::$instance = $container;
        static::$instance->instances[IContainer::class] = $container;
        static::$instance->components[IContainer::class] = get_class($container);
    }

    /**
     * @param  ReflectionFunctionAbstract $function
     * @param  array                      $params
     * @return mixed
     */
    protected function resolve(ReflectionFunctionAbstract $function, array $params)
    {
        $parameters = [];
        foreach ($function->getParameters() ?: [] as $parameter) {
            if ($class = $parameter->getClass()) {
                if (true === $this->has($class->getName())) {
                    $parameters[] = $this->get($class->getName());
                } else {
                    $parameters[] = $this->factory($class->getName());
                }
            } else {
                $name = $parameter->getName();
                if (array_key_exists($name, $params)) {
                    $parameters[] = $params[$name];
                } elseif ($parameter->isOptional()) {
                    $parameters[] = $parameter->getDefaultValue();
                } else {
                    $parameters[] = null;
                }
            }
        }
        return $parameters;
    }
}
