Compiled mapper
===

The compiled mapper is a special mapper aggregating specially-crafted mappers 
marked as compilable that can combine into a new native mapper and execute it. 

```php
<?php

use Kiboko\Component\FastMap\CompiledMapper;
use Kiboko\Component\FastMap\Compiler\Compiler;
use Kiboko\Component\FastMap\Compiler\StandardCompilationContext;
use Kiboko\Component\FastMap\Mapping\Composite;
use Kiboko\Component\FastMap\Mapping\Field;
use Kiboko\Component\FastMap\PropertyAccess\EmptyPropertyPath;
use Symfony\Component\PropertyAccess\PropertyPath;

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
    StandardCompilationContext::build(new EmptyPropertyPath(), 'var/mappers/', 'Foo\\BarMapper'),
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

$output = $mapper($input, [], new PropertyPath('[address]'));

// array(2) { "customer" => array(1) { "name" => "John Doe" }, "address" => "John P. Doe, Main Street, 42, 12345, Oblivion" }
```
