<?php

namespace Viloveul\Container;

use Exception;
use Psr\Container\NotFoundExceptionInterface as INotFoundException;

class NotFoundException extends Exception implements INotFoundException
{

}
