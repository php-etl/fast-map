<?php declare(strict_types=1);

namespace KibokoPhpSpecExtension;

use PhpSpec\Extension;
use PhpSpec\Factory\ReflectionFactory;
use PhpSpec\ServiceContainer;
use PhpSpec\ServiceContainer\IndexedServiceContainer;

final class ASTExtension implements Extension
{
    public function load(ServiceContainer $container, array $params)
    {
        $container->define('matchers.execute_compiled_mapping', function (IndexedServiceContainer $c) {
            return new Matcher\ExecuteCompiledMapping($c->get('formatter.presenter.value_presenter'));
        }, ['matchers']);

        $container->define('matchers.execute_uncompiled_mapping', function (IndexedServiceContainer $c) {
            return new Matcher\ExecuteUncompiledMapping($c->get('formatter.presenter.value_presenter'));
        }, ['matchers']);

        $container->define('matchers.throw_when_execute_uncompiled_mapping', function (IndexedServiceContainer $c) {
            return new Matcher\ThrowWhenExecuteCompiledMappingMatcher($c->get('unwrapper'), $c->get('formatter.presenter.value_presenter'), new ReflectionFactory());
        }, ['matchers']);
    }
}