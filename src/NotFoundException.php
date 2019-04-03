<?php

namespace Viloveul\Container;

use RuntimeException;
use Psr\Container\NotFoundExceptionInterface as INotFoundException;

class NotFoundException extends RuntimeException implements INotFoundException
{

}
