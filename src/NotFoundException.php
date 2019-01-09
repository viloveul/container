<?php

namespace Viloveul\Container;

use Psr\Container\NotFoundExceptionInterface as INotFoundException;
use RuntimeException;

class NotFoundException extends RuntimeException implements INotFoundException
{

}
