<?php declare(strict_types=1);

namespace Kiboko\Component\ETL\FastMap\Compiler\Builder;

use PhpParser\Builder;
use PhpParser\Node;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\PropertyAccess\PropertyPath;

final class SingleRelationFieldMapperBuilder implements Builder
{
    /** @var Expression */
    private $inputField;

    public function getNode(): Node
    {
        $inputPath = new PropertyPath($this->inputField);

        return array_merge(
            [
                (new RequiredValuePreconditionBuilder($inputPath, new Node\Expr\Variable('input'))),
                new Node\Stmt\Foreach_(
                    (new PropertyPathBuilder($inputPath, new Node\Expr\Variable('input')))->getNode(),
                    new Node\Expr\Variable('item'),
                    $this->inner->compile($outputNode)
                )
            ]
        );
    }
}