FastMap, a compiled data mapping library
===

Documentation
---

* [Array Mapping](docs/array-mapping.md)
* [Object Mapping](docs/object-mapping.md)
* [Automatic Compiled Mapper](docs/compiled-mapper.md)
* [Custom Compilation](docs/compilation.md)

Mapping Configuration Files
---

See https://github.com/php-etl/fast-map-config

Expression Language functions
---

* `collection(iterable $input): Kiboko\Component\ETL\FastMap\Collection\Collection`
* `locale(string ...$localeCodes): Kiboko\Component\ETL\FastMap\Collection\FilterInterface`
* `scope(string ...$scopeCodes): Kiboko\Component\ETL\FastMap\Collection\FilterInterface`
