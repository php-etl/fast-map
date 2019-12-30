<?php declare(strict_types=1);

namespace KibokoPhpSpecExtension\Matcher;

use PhpParser\Builder;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Exception\Example\NotEqualException;
use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\Matcher\BasicMatcher;

final class ExecuteCompiledTransformation extends BasicMatcher
{
    /**
     * @var Presenter
     */
    private $presenter;

    /**
     * @param Presenter $presenter
     */
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    public function supports(string $name, $subject, array $arguments): bool
    {
        return $name === 'executeCompiledTransformation' && count($arguments) == 3;
    }

    protected function matches($subject, array $arguments): bool
    {
        return $this->executeAST($subject, $arguments[0], $arguments[1]) === $arguments[2];
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
        return new NotEqualException(sprintf(
            'Expected %s, built from %s, but got %s.',
            $this->presenter->presentValue($arguments[0]),
            $this->presenter->presentValue($arguments[1]),
            $this->presenter->presentValue($arguments[2])
        ), $arguments[0], $arguments[1]);
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
        return new FailureException(sprintf(
            'Did not expect %s, built from %s, but got one.',
            $this->presenter->presentValue($arguments[1]),
            $this->presenter->presentValue($arguments[2])
        ));
    }

    private function executeAST($ast, $input, $output)
    {
        $functionName = '__' . hash('sha512', random_bytes(64)) . '__';

        $node = (new Builder\Function_($functionName))
            ->addParam((new Builder\Param('input'))->getNode())
            ->addParam((new Builder\Param('output'))->getNode())
            ->addStmts($ast)
            ->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('output')))
            ->getNode();

        include 'data://text/plain;base64,' . base64_encode((new Standard())->prettyPrintFile([$node]));

        try {
            return $functionName($input, $output);
        } catch (\Throwable $exception) {
            return new FailureException(sprintf(
                'Did not expect an exception of type %s, during execution of code: %s.',
                $this->presenter->presentException($exception),
                $this->presenter->presentValue((new Standard())->prettyPrint([$node]))
            ));
        }
    }
}