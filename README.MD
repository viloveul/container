# Installation
make sure your php version > 7.0
```bash
composer require viloveul/container
```
## How
```php
require __DIR__ . '/vendor/autoload.php';
$container = new Viloveul\Container\Container();
class YourClassA
{
	public $name = 'Hello';
}
class YourClassB
{
	public $name = null;
	public function __construct(YourClassA $a)
	{
		$this->name = $a->name;
	}
}
$result = $container->factory(YourClassB::class);
var_dump($result);
```
or
```php
require __DIR__ . '/vendor/autoload.php';
$container = new Viloveul\Container\Container();
interface EntityInterface
{
}
class YourClassA implements EntityInterface
{
	public $name = 'Hello';
}
class YourClassB
{
	public $name = null;
	public function __construct(EntityInterface $entity)
	{
		$this->name = $entity->name;
	}
}
$container->set(EntityInterface::class, YourClassA::class);
$result = $container->factory(YourClassB::class);
var_dump($result);
$entity = $container->get(EntityInterface::class);
var_dump($entity);
$container->set('world', function(EntityInterface $entity) {
	return new YourClassB($entity);
});
$world = $container->get('world');
var_dump($world);
$maybe = $container->invoke(function(EntityInterface $entity) {
	return $entity;
});
var_dump($maybe);
```