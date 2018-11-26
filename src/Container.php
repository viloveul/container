<?php

namespace Viloveul\Container;

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
    protected $classes = [];

    /**
     * @var mixed
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $instances = [];

    public function __construct()
    {
        static::setInstance($this);
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
     * @param  $class
     * @param  array    $params
     * @return mixed
     */
    public function factory($class, array $params = [])
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
     * @param  $id
     * @return mixed
     */
    public function get($id)
    {
        if (!array_key_exists($id, $this->instances)) {
            if ($this->has($id)) {
                if (is_callable($this->classes[$id])) {
                    $this->instances[$id] = $this->invoke($this->classes[$id]);
                } elseif (is_array($this->classes[$id])) {
                    $params = $this->classes[$id];
                    $class = $params['class'];
                    unset($params['class']);
                    $this->instances[$id] = $this->factory($class, $params);
                } else {
                    $this->instances[$id] = $this->factory($this->classes[$id]);
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
        return array_key_exists($id, $this->classes);
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
        if (is_string($param)) {
            if (!class_exists($param)) {
                throw new ContainerException("param \$param must be exists.");
            } else {
                $this->classes[$id] = $param;
            }
        } elseif (is_callable($param)) {
            $this->classes[$id] = $param;
        } elseif (is_array($param)) {
            if (!array_key_exists('class', $param) || !class_exists($param['class'])) {
                throw new ContainerException("param \$param must has index class and exists.");
            } else {
                $this->classes[$id] = $param;
            }
        } else {
            throw new ContainerException("param \$param must be string|callable|array.");
        }
    }

    /**
     * @param IContainer $container
     */
    public static function setInstance(IContainer $container)
    {
        static::$instance = $container;
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
