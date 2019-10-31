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

Mapping Configuration Files
---

See https://github.com/php-etl/rdf