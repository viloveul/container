<?php

namespace ViloveulContainerSample;

use Viloveul\Container\ContainerAwareTrait;
use Viloveul\Container\Contracts\ContainerAware;

class Injector implements ContainerAware
{
    use ContainerAwareTrait;
}
