<?php declare(strict_types=1);

namespace KibokoPhpSpecExtension;

use PhpSpec\Extension;
use PhpSpec\ServiceContainer;
use PhpSpec\ServiceContainer\IndexedServiceContainer;

final class ASTExtension implements Extension
{
    public function load(ServiceContainer $container, array $params)
    {
        $container->define('matchers.execute_compiled_transformation', function (IndexedServiceContainer $c) {
            return new Matcher\ExecuteCompiledTransformation($c->get('formatter.presenter'));
        }, ['matchers']);
    }
}