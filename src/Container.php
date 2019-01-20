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
use Viloveul\Container\Contracts\ContainerAware as IContainerAware;

class Container implements IContainer
{
    /**
     * @var array
     */
    protected $components = [];

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * @var mixed
     */
    protected static $instance = null;

    /**
     * @param array $definitions
     */
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
     * @param $id
     */
    public function get($id)
    {
        if (!array_key_exists($id, $this->components)) {
            if ($this->has($id) === true) {
                if (is_callable($this->definitions[$id]['target'])) {
                    $this->components[$id] = $this->invoke(
                        $this->definitions[$id]['target'],
                        $this->definitions[$id]['params']
                    );
                } else {
                    $this->components[$id] = $this->make(
                        $this->definitions[$id]['target'],
                        $this->definitions[$id]['params']
                    );
                }
            } else {
                throw new NotFoundException("{$id} does not found.");
            }
        }
        return $this->components[$id];
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
        return array_key_exists($id, $this->definitions);
    }

    /**
     * @param $function
     * @param array       $params
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
     * @param  string  $class
     * @param  array   $params
     * @return mixed
     */
    public function make(string $class, array $params = [])
    {
        try {
            $reflection = new ReflectionClass($class);
            if (!$reflection->isInstantiable()) {
                throw new ContainerException("{$class} is not instantiable.");
            }

            $constructor = $reflection->getConstructor();

            if (null === $constructor || $constructor->getNumberOfParameters() === 0) {
                $object = $reflection->newInstance();
            } else {
                $object = $reflection->newInstanceArgs($this->resolve($constructor, $params));
            }

            if ($object instanceof IContainerAware) {
                $object->setContainer($this);
            }

            return $object;

        } catch (ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * @param $id
     * @param $target
     * @param array     $params
     */
    public function map($id, $target, array $params = [])
    {
        if ($this->has($id) === true) {
            throw new ContainerException("ID {$id} already registered.");
        }
        if ($id === Closure::class) {
            throw new ContainerException("{$id} names cannot be registered.");
        }
        $this->definitions[$id] = compact('target', 'params');
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function raw($id)
    {
        if ($this->has($id)) {
            return $this->definitions[$id];
        }
        throw new NotFoundException("{$id} does not found.");
    }

    /**
     * @param $id
     * @param $target
     * @param array     $params
     */
    public function remap($id, $target, array $params = [])
    {
        if ($this->has($id) === false) {
            throw new ContainerException("ID {$id} hasn't been registered yet.");
        }
        if ($id === Closure::class) {
            throw new ContainerException("{$id} names cannot be registered.");
        }
        if (array_key_exists($id, $this->components)) {
            throw new ContainerException("{$id} names has been resolved.");
        }
        $this->definitions[$id] = compact('target', 'params');
    }

    /**
     * @param $id
     * @param $argument
     */
    public function set($id, $argument)
    {
        if (is_string($argument) || is_callable($argument)) {
            $this->map($id, $argument, []);
        } elseif (is_array($argument)) {
            $params = $argument;
            if (!array_key_exists('class', $params)) {
                throw new ContainerException("The second 'argument' must be an array with index 'class'.");
            } else {
                unset($params['class']);
                $this->map($id, $argument['class'], $params);
            }
        } else {
            throw new ContainerException("The second 'argument' must be an array, string, or callable.");
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
            $name = $parameter->getName();
            if ($class = $parameter->getClass()) {
                if ($this->has($class->getName()) === true) {
                    $parameters[] = $this->get($class->getName());
                } elseif ($class->isInstantiable() === true) {
                    $parameters[] = $this->make($class->getName());
                } elseif ($parameter->isOptional() !== true) {
                    if (array_key_exists($name, $params)) {
                        $parameters[] = $params[$name];
                    } elseif ($this->has($name) === true) {
                        $parameters[] = $this->get($name);
                    } else {
                        $parameters[] = null;
                    }
                } else {
                    $parameters[] = $parameter->getDefaultValue();
                }
            } else {
                if (array_key_exists($name, $params)) {
                    $parameters[] = $params[$name];
                } elseif ($parameter->isOptional() !== true && $this->has($name) === true) {
                    $parameters[] = $this->get($name);
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
