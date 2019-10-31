FastMap, a compiled data mapping library
===

Array Mappings
---

### Copy a field to another field

If you need to copy the value of an array field to the output array, use the `FieldCopyValueMapper` mapper.
This mapper will use `symfony/property-access` syntax to copy-paste values from the input to the output array.

```php
<?php

use Kiboko\Component\ETL\FastMap\FieldCopyValueMapper;

$input = [
    'customer' => [
        'first_name' => 'John',
    ], 
];

$mapper = new FieldCopyValueMapper('[first_name]', '[customer][first_name]');
var_dump($mapper($input, []));
// array(1) { "first_name" => "John" }
```

### Set a field to a constant value

If you need to set the value of an output array field, use the `FieldConstantValueMapper` mapper.
This mapper will use `symfony/property-access` syntax to set values to the output array.

```php
<?php

use Kiboko\Component\ETL\FastMap\FieldConstantValueMapper;

$input = [];

$mapper = new FieldConstantValueMapper('[customer][first_name]', 'John Doe');
$output = $mapper($input, []);
// array(1) { "first_name" => "John" }
```

### Concatenate fields to an unique output field

If you need to concatenate several values of the input array and set it to an unique field in the output array, use the `FieldConcatCopyValuesMapper` mapper.
This mapper will use `symfony/property-access` syntax to copy-paste values from the input to the output array.

```php
<?php

use Kiboko\Component\ETL\FastMap\FieldConcatCopyValuesMapper;

$input = [
    'customerAddress' => [
        'name' => 'John P. Doe',
        'street' => 'Main Street, 42',
        'city' => 'Oblivion',
        'postcode' => '12345',
    ]   
];

$mapper = new FieldConcatCopyValuesMapper(
    '[address]',
     ', ',
    '[customerAddress][name]',
    '[customerAddress][street]',
    '[customerAddress][postcode]',
    '[customerAddress][city]'
);
$output = $mapper($input, []);
// array(1) { "address" => "John P. Doe, Main Street, 42, 12345, Oblivion" }
```

Object Mappings
---

Compiled mapper
---

The compiled mapper is a special mapper aggregating specially-crafted mappers 
marked as compilable that can combine into a new native mapper and execute it. 

```php
<?php

use Kiboko\Component\ETL\FastMap\CompiledMapper;
use Kiboko\Component\ETL\FastMap\Compiler\Compiler;
use Kiboko\Component\ETL\FastMap\FieldConcatCopyValuesMapper;
use Kiboko\Component\ETL\FastMap\FieldConstantValueMapper;

$input = [
    'customerAddress' => [
        'name' => 'John P. Doe',
        'street' => 'Main Street, 42',
        'city' => 'Oblivion',
        'postcode' => '12345',
    ]   
];

$compiler = new Compiler();

$mapper = new CompiledMapper(
    $compiler,
    'Foo\\BarMapper',
    'var/mappers/',
    new FieldConstantValueMapper('[customer][first_name]', 'John Doe'),
    new FieldConcatCopyValuesMapper(
        '[address]',
         ', ',
        '[customerAddress][name]',
        '[customerAddress][street]',
        '[customerAddress][postcode]',
        '[customerAddress][city]'
    )
);

$output = $mapper($input, []);

// array(2) { "customer" => array(1) { "first_name" => "John Doe" }, "address" => "John P. Doe, Main Street, 42, 12345, Oblivion" }
```

Compile a mapper into a dedicated class
---

### Using the Spaghetti code compilation strategy

This strategy will produce a mapper with an unique method, with every mapping instruction into it.
This will produce spaghetti code. It is the default strategy.

Considering you have the following script:

```php
<?php

use Kiboko\Component\ETL\FastMap\Compiler;
use Kiboko\Component\ETL\FastMap\FieldConcatCopyValuesMapper;
use Kiboko\Component\ETL\FastMap\FieldConstantValueMapper;

$compiler = new Compiler\Strategy\Spaghetti();
$tree = $compiler->buildTree(
    'Foo\\Component\\',
    'FooMapper',
    new FieldConstantValueMapper('[customer][first_name]', 'John Doe'),
    new FieldConcatCopyValuesMapper(
        '[address]',
         ', ',
        '[customerAddress][name]',
        '[customerAddress][street]',
        '[customerAddress][postcode]',
        '[customerAddress][city]'
    )
);

file_put_contents('./FooMapper.php', $prettyPrinter->prettyPrintFile($tree));
```

Will result in the generation of the following class:

```php
<?php

namespace Foo\Component;

final class BarMapper implements \Kiboko\Component\ETL\FastMap\Contracts\MapperInterface
{
    public function __invoke($input, $output)
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

### Using the Reduce code compilation strategy

This strategy will produce a mapper with as much methods there as there were mappers compiled to produce it.
It will call every method and apply an array reduce to combine each result.

Considering you have the following script:

```php
<?php

use Kiboko\Component\ETL\FastMap\Compiler;
use Kiboko\Component\ETL\FastMap\FieldConcatCopyValuesMapper;
use Kiboko\Component\ETL\FastMap\FieldConstantValueMapper;

$compiler = new Compiler\Strategy\Reduce();
$tree = $compiler->buildTree(
    'Foo\\Component\\',
    'FooMapper',
    new FieldConstantValueMapper('[customer][first_name]', 'John Doe'),
    new FieldConcatCopyValuesMapper(
        '[address]',
         ', ',
        '[customerAddress][name]',
        '[customerAddress][street]',
        '[customerAddress][postcode]',
        '[customerAddress][city]'
    )
);

file_put_contents('./FooMapper.php', $prettyPrinter->prettyPrintFile($tree));
```

Will result in the generation of the following class:

```php
<?php

namespace Foo\Component;

final class FooMapper implements \Kiboko\Component\ETL\FastMap\Contracts\MapperInterface
{
    public function __invoke($input, $output)
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

Mapping Configuration Files
---

See https://github.com/php-etl/rdf