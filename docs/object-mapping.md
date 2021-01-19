Object Mappings
===

Mapping definition building
---

The object mapping is more complex than what we can see when using arrays,
let it be object creation or accessing to virtual properties made up from
get/set methods (eg. `getFoo()` and `setFoo()` for a virtual `foo` property).

In order to make it more comfortable while doing various mapping operations,
we will need to declare the way we want our mapping to be done.

Here comes the `MappingDefinition` sub-package. It is a set of data objects and
factories respectively for defining your object structure and building this 
structure with reflection.

Here is the basic mapping factory:

Then, the factory can be used to generate your mapping definition.

```php
<?php
use Kiboko\Component\FastMap\MappingIteration;
use Kiboko\Component\Metadata;

/** @var Metadata\ClassTypeMetadata $metadata */
$metadata = (new Metadata\ClassMetadataBuilder())->buildFromFQCN('Lorem\\Ipsum\\Dolor');

/** @var MappingIteration\MappedClassTypeFactory $mappingFactory */
$mappingDefinition = $mappingFactory($metadata);
```
