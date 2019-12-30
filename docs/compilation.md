Compile a mapper into a dedicated class
===

Using the Spaghetti code compilation strategy
---

This strategy will produce a mapper with an unique method, with every mapping instruction into it.
This will produce spaghetti code. It is the default strategy.

Considering you have the following script:

```php
<?php

use Kiboko\Component\ETL\FastMap\Compiler;
use Kiboko\Component\ETL\FastMap\PropertyAccess\EmptyPropertyPath;
use Kiboko\Component\ETL\FastMap\Mapping\Composite;
use Kiboko\Component\ETL\FastMap\Mapping\Field;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Symfony\Component\PropertyAccess\PropertyPath;

$compiler = new Compiler\Strategy\Spaghetti();
$tree = $compiler->buildTree(
    new EmptyPropertyPath(),
    new ClassTypeMetadata('Foo\\Component\\FooMapper'),
    new Composite\ArrayMapper(
        new Field(
            new PropertyPath('[customer][name]'),
            new Field\ConstantValueMapper('John Doe')
        ),
        new Field(
            new PropertyPath('[address]'),
            new Field\ConcatCopyValuesMapper(
                ', ',
                new PropertyPath('[customerAddress][name]'),
                new PropertyPath('[customerAddress][street]'),
                new PropertyPath('[customerAddress][postcode]'),
                new PropertyPath('[customerAddress][city]')
            )
        )
    )
);

file_put_contents('./FooMapper.php', $prettyPrinter->prettyPrintFile($tree));
```

Will result in the generation of the following class:

```php
<?php

namespace Foo\Component;

final class BarMapper implements \Kiboko\Component\ETL\FastMap\Contracts\CompiledMapperInterface
{
    public function __invoke($input, $output = null)
    {
        if (!isset($output)) {
            $output = [];
        }
        if (!isset($output['customer'])) {
            $output['customer'] = [];
        }
        $output['customer']['first_name'] = 'John Doe';
        if (!isset($input['customerAddress']['name'])) {
            throw new \RuntimeException('Could not evaluate path [customerAddress][name]');
        }
        if (!isset($input['customerAddress']['street'])) {
            throw new \RuntimeException('Could not evaluate path [customerAddress][street]');
        }
        if (!isset($input['customerAddress']['postcode'])) {
            throw new \RuntimeException('Could not evaluate path [customerAddress][postcode]');
        }
        if (!isset($input['customerAddress']['city'])) {
            throw new \RuntimeException('Could not evaluate path [customerAddress][city]');
        }
        if (!isset($output)) {
            $output = [];
        }
        $output['address'] = $input['customerAddress']['name'] . ', ' . $input['customerAddress']['street'] . ', ' . $input['customerAddress']['postcode'] . ', ' . $input['customerAddress']['city'];
        return $output;
    }
}
```

Using the Reduce code compilation strategy
---

This strategy will produce a mapper with as much methods there as there were mappers compiled to produce it.
It will call every method and apply an array reduce to combine each result.

Considering you have the following script:

```php
<?php

use Kiboko\Component\ETL\FastMap\Compiler;
use Kiboko\Component\ETL\FastMap\PropertyAccess\EmptyPropertyPath;
use Kiboko\Component\ETL\FastMap\Mapping\Composite;
use Kiboko\Component\ETL\FastMap\Mapping\Field;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Symfony\Component\PropertyAccess\PropertyPath;

$compiler = new Compiler\Strategy\Reduce();
$tree = $compiler->buildTree(
    new EmptyPropertyPath(),
    new ClassTypeMetadata('Foo\\Component\\FooMapper'),
    new Composite\ArrayMapper(
        new Field(
            new PropertyPath('[customer][name]'),
            new Field\ConstantValueMapper('John Doe')
        ),
        new Field(
            new PropertyPath('[address]'),
            new Field\ConcatCopyValuesMapper(
                ', ',
                new PropertyPath('[customerAddress][name]'),
                new PropertyPath('[customerAddress][street]'),
                new PropertyPath('[customerAddress][postcode]'),
                new PropertyPath('[customerAddress][city]')
            )
        )
    )
);

file_put_contents('./FooMapper.php', $prettyPrinter->prettyPrintFile($tree));
```

Will result in the generation of the following class:

```php
<?php

namespace Foo\Component;

final class FooMapper implements \Kiboko\Component\ETL\FastMap\Contracts\CompiledMapperInterface
{
    public function __invoke($input, $output = null)
    {
        return array_reduce([
            $this->map_32e78e83d3b4aa286bc891ccfd4c44ed942476649678aa72d668f35db7223ad1($input, $output),
            $this->map_7ea78d7b1e8f122866ef7aa7cd7190ebb5f88836c241fc328f267abb9e5d45e5($input, $output)
        ], function ($current, $initial) {
            return array_merge($initial, $current);
        }, []);
    }
    private final function map_32e78e83d3b4aa286bc891ccfd4c44ed942476649678aa72d668f35db7223ad1($input, $output)
    {
        if (!isset($output)) {
            $output = [];
        }
        if (!isset($output['customer'])) {
            $output['customer'] = [];
        }
        $output['customer']['first_name'] = 'John Doe';
        return $output;
    }
    private final function map_7ea78d7b1e8f122866ef7aa7cd7190ebb5f88836c241fc328f267abb9e5d45e5($input, $output)
    {
        if (!isset($input['customerAddress']['name'])) {
            throw new \RuntimeException('Could not evaluate path [customerAddress][name]');
        }
        if (!isset($input['customerAddress']['street'])) {
            throw new \RuntimeException('Could not evaluate path [customerAddress][street]');
        }
        if (!isset($input['customerAddress']['postcode'])) {
            throw new \RuntimeException('Could not evaluate path [customerAddress][postcode]');
        }
        if (!isset($input['customerAddress']['city'])) {
            throw new \RuntimeException('Could not evaluate path [customerAddress][city]');
        }
        if (!isset($output)) {
            $output = [];
        }
        $output['address'] = $input['customerAddress']['name'] . ', ' . $input['customerAddress']['street'] . ', ' . $input['customerAddress']['postcode'] . ', ' . $input['customerAddress']['city'];
        return $output;
    }
}
```
