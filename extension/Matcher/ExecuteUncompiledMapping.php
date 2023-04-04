<?php

declare(strict_types=1);

namespace KibokoPhpSpecExtension\Matcher;

use Kiboko\Contract\Mapping\MapperInterface;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Exception\Example\NotEqualException;
use PhpSpec\Formatter\Presenter\Value\ValuePresenter;
use PhpSpec\Matcher\BasicMatcher;

final readonly class ExecuteUncompiledMapping extends BasicMatcher
{
    public function __construct(private ValuePresenter $presenter)
    {
    }

    public function supports(string $name, $subject, array $arguments): bool
    {
        return 'executeUncompiledMapping' === $name && 3 == \count($arguments);
    }

    /**
     * @param MapperInterface $subject
     */
    protected function matches($subject, array $arguments): bool
    {
        [$input, $output, $expected] = $arguments;

        return $subject($input, $output) == $expected;
    }

    /**
     * @return NotEqualException
     */
    protected function getFailureException(string $name, mixed $subject, array $arguments): FailureException
    {
        [$input, $output, $expected] = $arguments;

        return new NotEqualException(sprintf(
            'Expected %s, built from %s, but got %s.',
            $this->presenter->presentValue($expected),
            $this->presenter->presentValue($input),
            $this->presenter->presentValue($output)
        ), $expected, $output);
    }

    protected function getNegativeFailureException(string $name, mixed $subject, array $arguments): FailureException
    {
        [$input, $output, $expected] = $arguments;

        return new FailureException(sprintf(
            'Did not expect %s, built from %s, but got one.',
            $this->presenter->presentValue($output),
            $this->presenter->presentValue($expected)
        ));
    }
}
