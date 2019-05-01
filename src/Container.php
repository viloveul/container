<?php

namespace Viloveul\Container;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionException;
use ReflectionFunctionAbstract;
use Viloveul\Container\ContainerException;
use Viloveul\Container\Contracts\Container as IContainer;
use Viloveul\Container\Contracts\ContainerAware as IContainerAware;

class Container implements IContainer
{
    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var array
     */
    protected $components = [];

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * @param  $id
     * @return mixed
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * @param string $id
     * @param string $target
     */
    public function alias(string $id, string $target): void
    {
        if (array_key_exists($id, $this->definitions)) {
            throw new ContainerException("ID {$id} already registered as definitions.");
        }
        if (array_key_exists($id, $this->aliases)) {
            throw new ContainerException("ID {$id} already registered as aliases for " . $this->getRealIdentifier($id));
        }
        $this->aliases[$id] = $target;
    }

    /**
     * @param string $id
     * @param bool   $recursive
     */
    public function forget(string $id, bool $recursive = false): void
    {
        if (array_key_exists($id, $this->definitions)) {
            $this->definitions[$id] = null;
            unset($this->definitions[$id]);
            if (array_key_exists($id, $this->components)) {
                $this->components[$id] = null;
                unset($this->components[$id]);
            }
        }
        if (array_key_exists($id, $this->aliases)) {
            if ($recursive === true) {
                $this->forget($this->aliases[$id], $recursive);
            }
            $this->aliases[$id] = null;
            unset($this->aliases[$id]);
        }
    }

    /**
     * @param $id
     */
    public function get($name)
    {
        $id = $this->getRealIdentifier($name);
        if (!array_key_exists($id, $this->components)) {
            if (array_key_exists($id, $this->definitions) === true) {
                $definition = $this->definitions[$id];
                if (is_callable($definition['target'])) {
                    $this->components[$id] = $this->invoke($definition['target'], $definition['params']);
                } else {
                    $this->components[$id] = $this->make($definition['target'], $definition['params']);
                }
            } else {
                throw new NotFoundException("{$id} does not found.");
            }
        }
        return $this->components[$id];
    }

    /**
     * @param  string  $name
     * @return mixed
     */
    public function getRealIdentifier(string $name): string
    {
        while (array_key_exists($name, $this->aliases) === true) {
            $name = $this->aliases[$name];
        }
        return $name;
    }

    /**
     * @param $id
     */
    public function has($id)
    {
        return array_key_exists($this->getRealIdentifier($id), $this->definitions);
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
    public function map($id, $target, array $params = []): void
    {
        if (array_key_exists($id, $this->definitions) === true) {
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
    public function remap($id, $target, array $params = []): void
    {
        if (array_key_exists($id, $this->definitions) === false) {
            throw new ContainerException("ID {$id} hasn't been registered yet.");
        }
        if ($id === Closure::class) {
            throw new ContainerException("{$id} names cannot be registered.");
        }
        if (array_key_exists($id, $this->components)) {
            $this->forget($id, false);
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
