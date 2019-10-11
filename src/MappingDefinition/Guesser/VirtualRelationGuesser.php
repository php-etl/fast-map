<?php

namespace Kiboko\Component\ETL\FastMap\MappingDefinition\Guesser;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\VirtualMultipleRelationDefinition;
use Kiboko\Component\ETL\FastMap\MappingDefinition\Relation\VirtualUnaryRelationDefinition;
use Kiboko\Component\ETL\Metadata\ClassTypeMetadata;
use Kiboko\Component\ETL\Metadata\MethodMetadata;
use Kiboko\Component\ETL\Metadata\ScalarTypeMetadata;
use Kiboko\Component\ETL\Metadata\Type;

class VirtualRelationGuesser implements RelationDefinitionGuesserInterface
{
    /** @var Inflector */
    private $inflector;

    public function __construct()
    {
        $this->inflector = (new English\InflectorFactory())();
    }

    private function isPlural(string $field): bool
    {
        return $this->inflector->pluralize($field) === $field;
    }

    private function isSingular(string $field): bool
    {
        return $this->inflector->singularize($field) === $field;
    }

    public function __invoke(ClassTypeMetadata $class): \Generator
    {
        $methodCandidates = [];
        /** @var MethodMetadata $method */
        foreach ($class->methods as $method) {
            if (preg_match('/(?<action>set|remove|add|has)(?<relationName>[a-zA-Z_][a-zA-Z0-9_]*)/', $method->name, $matches) &&
                count($method->argumentList->arguments) === 1
            ) {
                $action = $matches['action'];
                $relationName = $this->inflector->camelize($matches['relationName']);
                if (!isset($methodCandidates[$relationName])) {
                    $methodCandidates[$relationName] = [];
                }

                $methodCandidates[$relationName][$action] = $method;
            } else if (preg_match('/(?<action>unset|get)(?<relationName>[a-zA-Z_][a-zA-Z0-9_]*)/', $method->name, $matches) &&
                count($method->argumentList->arguments) === 0
            ) {
                $action = $matches['action'];
                $relationName = $this->inflector->camelize($matches['relationName']);
                if (!isset($methodCandidates[$relationName])) {
                    $methodCandidates[$relationName] = [];
                }

                $methodCandidates[$relationName][$action] = $method;
            } else if (preg_match('/count(?<relationName>[a-zA-Z_][a-zA-Z0-9_]*)/', $method->name, $matches) &&
                Type::isOneOf(new ScalarTypeMetadata('integer'), $method->returnTypes) &&
                count($method->argumentList->arguments) === 0
            ) {
                $relationName = $this->inflector->camelize($matches['relationName']);
                if (!isset($methodCandidates[$relationName])) {
                    $methodCandidates[$relationName] = [];
                }

                $methodCandidates[$relationName]['count'] = $method;
            } else if (preg_match('/walk(?<relationName>[a-zA-Z_][a-zA-Z0-9_]*)/', $method->name, $matches) &&
                Type::isOneOf(new ScalarTypeMetadata('iterable'), $method->returnTypes) &&
                count($method->argumentList->arguments) === 0
            ) {
                $relationName = $this->inflector->camelize($matches['relationName']);
                if (!isset($methodCandidates[$relationName])) {
                    $methodCandidates[$relationName] = [];
                }

                $methodCandidates[$relationName]['walk'] = $method;
            }
        }

        foreach ($methodCandidates as $relationName => $actions) {
            if ($this->isPlural($relationName)) {
                yield new VirtualMultipleRelationDefinition(
                    $relationName,
                    $actions['get'] ?? null,
                    $actions['set'] ?? null,
                    $actions['add'] ?? null,
                    $actions['remove'] ?? null,
                    $actions['walk'] ?? null,
                    $actions['count'] ?? null
                );
            }
            if ($this->isSingular($relationName)) {
                yield new VirtualUnaryRelationDefinition(
                    $relationName,
                    $actions['get'] ?? $actions['is'] ?? null,
                    $actions['set'] ?? null,
                    $actions['has'] ?? null,
                    $actions['unset'] ?? null
                );
            }
        }
    }
}