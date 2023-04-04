<?php

declare(strict_types=1);

namespace KibokoPhpSpecExtension\Matcher;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Exception\Example\MatcherException;
use PhpSpec\Exception\Example\NotEqualException;
use PhpSpec\Exception\Fracture\MethodNotFoundException;
use PhpSpec\Factory\ReflectionFactory;
use PhpSpec\Formatter\Presenter\Value\ValuePresenter;
use PhpSpec\Matcher\Matcher;
use PhpSpec\Wrapper\DelayedCall;
use PhpSpec\Wrapper\Unwrapper;

final class ThrowWhenExecuteCompiledMappingMatcher implements Matcher
{
    use ASTExecutionAwareTrait;

    private static array $ignoredProperties = ['file', 'line', 'string', 'trace', 'previous'];

    public function __construct(private readonly Unwrapper $unwrapper, private readonly ValuePresenter $presenter, private readonly ?ReflectionFactory $factory)
    {
    }

    public function supports(string $name, mixed $subject, array $arguments): bool
    {
        return 'throwWhenExecuteCompiledMapping' === $name;
    }

    public function positiveMatch(string $name, mixed $subject, array $arguments): DelayedCall
    {
        return $this->getDelayedCall($this->verifyPositive(...), $subject, $arguments);
    }

    public function negativeMatch(string $name, mixed $subject, array $arguments): DelayedCall
    {
        return $this->getDelayedCall($this->verifyNegative(...), $subject, $arguments);
    }

    /**
     * @param mixed|null $exception
     *
     * @throws \PhpSpec\Exception\Example\FailureException
     * @throws \PhpSpec\Exception\Example\NotEqualException
     */
    public function verifyPositive(mixed $subject, array $arguments, $exception = null): void
    {
        [$input, $output] = $arguments;
        $exceptionThrown = null;

        try {
            $this->executeStatements($subject, $input, $output);
        } catch (\Exception|\Throwable $e) {
            $exceptionThrown = $e;
        }

        if (!$exceptionThrown) {
            throw new FailureException('Expected to get exception / throwable, none got.');
        }

        if (null === $exception) {
            return;
        }

        if (!$exceptionThrown instanceof $exception) {
            $format = 'Expected exception of class %s, but got %s.';

            if ($exceptionThrown instanceof \Error) {
                $format = 'Expected exception of class %s, but got %s with the message: "%s"';
            }

            throw new FailureException(sprintf($format, $this->presenter->presentValue($exception), $this->presenter->presentValue($exceptionThrown), $exceptionThrown->getMessage()));
        }

        if (\is_object($exception)) {
            $exceptionRefl = $this->factory->create($exception);
            foreach ($exceptionRefl->getProperties() as $property) {
                if (\in_array($property->getName(), self::$ignoredProperties, true)) {
                    continue;
                }

                $property->setAccessible(true);
                $expected = $property->getValue($exception);
                $actual = $property->getValue($exceptionThrown);

                if (null !== $expected && $actual !== $expected) {
                    throw new NotEqualException(sprintf('Expected exception `%s` to be %s, but it is %s.', $property->getName(), $this->presenter->presentValue($expected), $this->presenter->presentValue($actual)), $expected, $actual);
                }
            }
        }
    }

    /**
     * @param mixed|null $exception
     *
     * @throws \PhpSpec\Exception\Example\FailureException
     */
    public function verifyNegative(mixed $subject, array $arguments, $exception = null): void
    {
        [$input, $output] = $arguments;
        $exceptionThrown = null;

        try {
            $this->executeStatements($subject, $input, $output);
        } catch (\Exception|\Throwable $e) {
            $exceptionThrown = $e;
        }

        if ($exceptionThrown && null === $exception) {
            throw new FailureException(sprintf('Expected to not throw any exceptions, but got %s.', $this->presenter->presentValue($exceptionThrown)));
        }

        if ($exceptionThrown && $exceptionThrown instanceof $exception) {
            $invalidProperties = [];
            if (\is_object($exception)) {
                $exceptionRefl = $this->factory->create($exception);
                foreach ($exceptionRefl->getProperties() as $property) {
                    if (\in_array($property->getName(), self::$ignoredProperties, true)) {
                        continue;
                    }

                    $property->setAccessible(true);
                    $expected = $property->getValue($exception);
                    $actual = $property->getValue($exceptionThrown);

                    if (null !== $expected && $actual === $expected) {
                        $invalidProperties[] = sprintf(
                            '  `%s`=%s',
                            $property->getName(),
                            $this->presenter->presentValue($expected)
                        );
                    }
                }
            }

            $withProperties = '';
            if (\count($invalidProperties) > 0) {
                $withProperties = sprintf(
                    ' with'.\PHP_EOL.'%s,'.\PHP_EOL,
                    implode(",\n", $invalidProperties)
                );
            }

            throw new FailureException(sprintf('Expected to not throw %s exception%s but got it.', $this->presenter->presentValue($exception), $withProperties));
        }
    }

    public function getPriority(): int
    {
        return 1;
    }

    private function getDelayedCall(callable $check, mixed $subject, array $arguments): DelayedCall
    {
        $exception = $this->getException($arguments);
        $unwrapper = $this->unwrapper;

        return new DelayedCall(
            function ($method, $arguments) use ($check, $subject, $exception, $unwrapper) {
                $arguments = $unwrapper->unwrapAll($arguments);

                $methodName = $arguments[0];
                $arguments = $arguments[1] ?? [];
                $callable = [$subject, $methodName];

                [$class, $methodName] = [$subject, $methodName];
                if (!method_exists($class, $methodName) && !method_exists($class, '__call')) {
                    throw new MethodNotFoundException(sprintf('Method %s::%s not found.', $class::class, $methodName), $class, $methodName, $arguments);
                }

                return \call_user_func($check, $callable, $arguments, $exception);
            }
        );
    }

    /**
     * @throws \PhpSpec\Exception\Example\MatcherException
     */
    private function getException(array $arguments): string|\Throwable|null
    {
        if (0 === \count($arguments)) {
            return null;
        }

        if (\is_string($arguments[0])) {
            return $arguments[0];
        }

        if (\is_object($arguments[0])) {
            if ($arguments[0] instanceof \Throwable) {
                return $arguments[0];
            }
        }

        throw new MatcherException(sprintf("Wrong argument provided in throw matcher.\nFully qualified classname or exception instance expected,\n".'Got %s.', $this->presenter->presentValue($arguments[0])));
    }
}
