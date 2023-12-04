<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Compiler\Builder;

use Kiboko\Component\SatelliteToolbox\Builder\PropertyPathBuilder;

trigger_deprecation('php-etl/fast-map', '0.5.2', 'The "%s" class is deprecated, use "%s" instead.', 'Kiboko\\Component\\FastMap\\Compiler\\Builder\\PropertyPathBuilder', PropertyPathBuilder::class);

/*
 * @deprecated since FastMap 0.5.2, use Kiboko\Component\SatelliteToolbox\Builder\PropertyPathBuilder instead.
 */
class_alias(PropertyPathBuilder::class, 'Kiboko\\Component\\FastMap\\Compiler\\Builder\\PropertyPathBuilder');
