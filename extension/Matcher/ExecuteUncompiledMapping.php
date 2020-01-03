<?php declare(strict_types=1);

namespace KibokoPhpSpecExtension\Matcher;

use Kiboko\Component\ETL\FastMap\Contracts\MapperInterface;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Exception\Example\NotEqualException;
use PhpSpec\Formatter\Presenter\Value\ValuePresenter;
use PhpSpec\Matcher\BasicMatcher;

final class ExecuteUncompiledMapping extends BasicMatcher
{
    /** @var ValuePresenter */
    private $presenter;

    /**
     * @param ValuePresenter $presenter
     */
    public function __construct(ValuePresenter $presenter)
    {
        $this->presenter = $presenter;
    }

    public function supports(string $name, $subject, array $arguments): bool
    {
        return $name === 'executeUncompiledMapping' && count($arguments) == 3;
    }

    /**
     * @param MapperInterface $subject
     */
    protected function matches($subject, array $arguments): bool
    {
        list($input, $output, $expected) = $arguments;

        return $subject($input, $output) == $expected;
    }

    /**
     * @param string $name
     * @param mixed  $subject
     * @param array  $arguments
     *
     * @return NotEqualException
     */
    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        list($input, $output, $expected) = $arguments;

        return new NotEqualException(sprintf(
            'Expected %s, built from %s, but got %s.',
            $this->presenter->presentValue($expected),
            $this->presenter->presentValue($input),
            $this->presenter->presentValue($output)
        ), $expected, $output);
    }

    /**
     * @param string $name
     * @param mixed  $subject
     * @param array  $arguments
     *
     * @return FailureException
     */
    protected function getNegativeFailureException(string $name, $subject, array $arguments): FailureException
    {
        list($input, $output, $expected) = $arguments;

        return new FailureException(sprintf(
            'Did not expect %s, built from %s, but got one.',
            $this->presenter->presentValue($output),
            $this->presenter->presentValue($expected)
        ));
    }
}