<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

trigger_deprecation('php-etl/fast-map', '0.5', 'The "%s" class is deprecated, use "%s" instead.', 'Kiboko\\Component\\FastMap\\Compiler\\Builder\\PropertyPathBuilder', \Kiboko\Component\SatelliteToolbox\Builder\PropertyPathBuilder::class);

/*
 * @deprecated since FastMap 0.5, use Kiboko\Component\SatelliteToolbox\Builder\PropertyPathBuilder instead.
 */
class_alias(\Kiboko\Component\SatelliteToolbox\Builder\PropertyPathBuilder::class, 'Kiboko\\Component\\FastMap\\Compiler\\Builder\\PropertyPathBuilder');
