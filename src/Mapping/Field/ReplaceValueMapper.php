<?php

declare(strict_types=1);

namespace Kiboko\Component\FastMap\Mapping\Field;

use Kiboko\Component\FastMap\Compiler\Builder\PropertyPathBuilder;
use Kiboko\Component\FastMap\Compiler\Builder\RequiredValuePreconditionBuilder;
use Kiboko\Contract\Mapping;
use PhpParser\Node;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

final class ReplaceValueMapper implements Mapping\FieldMapperInterface, Mapping\CompilableMapperInterface
{
    private readonly PropertyAccessor $accessor;
    /** @var Node\Expr\Variable[] */
    private iterable $contextVariables = [];

    public function __construct(private readonly PropertyPathInterface $inputPaths, private readonly array $replacements = [])
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function __invoke($input, $output, PropertyPathInterface $outputPath)
    {
        $this->accessor->setValue(
            $output,
            $outputPath,
            $this->accessor->getValue($input, $this->inputPaths) ?? null
        );

        return $output;
    }

    public function addContextVariable(Node\Expr\Variable $variable): self
    {
        $this->contextVariables[] = $variable;

        return $this;
    }

    public function compile(Node\Expr $outputNode): array
    {
        $inputPath = new PropertyPath($this->inputPaths);
        $inputNode = (new PropertyPathBuilder($inputPath, new Node\Expr\Variable('input')))->getNode();

        $patterns = [];
        $replacements = [];
        foreach ($this->replacements as $pattern => $replacement) {
            $patterns[] = new Node\Expr\ArrayItem(new Node\Scalar\String_($pattern));
            $replacements[] = new Node\Expr\ArrayItem(new Node\Scalar\String_($replacement));
        }

        return [
            (new RequiredValuePreconditionBuilder($inputPath, new Node\Expr\Variable('input')))->getNode(),

            new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    $outputNode,
                    new Node\Expr\FuncCall(
                        name: new Node\Name('json_decode'),
                        args: [
                            new Node\Expr\FuncCall(
                                name: new Node\Name('str_replace'),
                                args: [
                                    new Node\Arg(new Node\Expr\Array_($patterns)),
                                    new Node\Arg(new Node\Expr\Array_($replacements)),
                                    new Node\Expr\FuncCall(
                                        name: new Node\Name('json_encode'),
                                        args: [$inputNode]
                                    )
                                ]
                            ),
                            new Node\Name('true')
                        ]
                    )
                ),
            ),
        ];
    }
}
