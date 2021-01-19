Array Mappings
===

Copy a field to another field
---

If you need to copy the value of an array field to the output array, use the `FieldCopyValueMapper` mapper.
This mapper will use `symfony/property-access` syntax to copy-paste values from the input to the output array.

```php
<?php

use Kiboko\Component\FastMap\Mapping\Field\CopyValueMapper;
use Symfony\Component\PropertyAccess\PropertyPath;

$input = [
    'customer' => [
        'first_name' => 'John',
    ], 
];

$mapper = new CopyValueMapper(
    new PropertyPath('[customer][first_name]')
);
var_dump($mapper($input, [], new PropertyPath('[first_name]')));
// array(1) { "first_name" => "John" }
```

Set a field to a constant value
---

If you need to set the value of an output array field, use the `FieldConstantValueMapper` mapper.
This mapper will use `symfony/property-access` syntax to set values to the output array.

```php
<?php

use Kiboko\Component\FastMap\Mapping\Field\ConstantValueMapper;
use Symfony\Component\PropertyAccess\PropertyPath;

$input = [];

$mapper = new ConstantValueMapper('John Doe');
$output = $mapper($input, [], new PropertyPath('[customer][first_name]'));
// array(1) { "first_name" => "John" }
```

Concatenate fields to an unique output field
---

If you need to concatenate several values of the input array and set it to an unique field in the output array, use the `FieldConcatCopyValuesMapper` mapper.
This mapper will use `symfony/property-access` syntax to copy-paste values from the input to the output array.

```php
<?php

use Kiboko\Component\FastMap\Mapping\Field\ConcatCopyValuesMapper;
use Symfony\Component\PropertyAccess\PropertyPath;

$input = [
    'customerAddress' => [
        'name' => 'John P. Doe',
        'street' => 'Main Street, 42',
        'city' => 'Oblivion',
        'postcode' => '12345',
    ]   
];

$mapper = new ConcatCopyValuesMapper(
     ', ',
    new PropertyPath('[customerAddress][name]'),
    new PropertyPath('[customerAddress][street]'),
    new PropertyPath('[customerAddress][postcode]'),
    new PropertyPath('[customerAddress][city]')
);
$output = $mapper($input, [], new PropertyPath('[address]'));
// array(1) { "address" => "John P. Doe, Main Street, 42, 12345, Oblivion" }
```

Use Symfony's Expression Language
---

If you need to do more complex stuff, the integration with the `symfony/expression-language` is integrated into the `FieldExpressionLanguageValueMapper` mapper.
This mapper will use `symfony/property-access` syntax to copy-paste values from the input to the output array.

```php
<?php

use Kiboko\Component\FastMap\Mapping\Field\ExpressionLanguageValueMapper;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\PropertyAccess\PropertyPath;

$interpreter = new ExpressionLanguage();

$input = [
    'ean' => '1234567890128',
    'weight' => [
        'value' => 5,
        'unit' => 'POUNDS',
    ],
    'qty' => 23,
];

$mapper = new ExpressionLanguageValueMapper(
    $interpreter,
    new Expression('input["weight"]["unit"] == "POUNDS" ? (input["weight"]["value"] / 2.205) : input["weight"]["value"]'),
);
$output = $mapper($input, [], new PropertyPath('[weight]'));
// array(1) { "weight" => 2.26796 }
```
