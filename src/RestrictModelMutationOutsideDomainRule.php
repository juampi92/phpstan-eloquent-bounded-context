<?php

namespace Juampi92\PHPStanEloquentBoundedContext;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class RestrictModelMutationOutsideDomainRule implements Rule
{
    private DomainResolver $domainResolver;

    public function __construct(DomainResolver $domainResolver)
    {
        $this->domainResolver = $domainResolver;
    }

    public function getNodeType(): string
    {
        return Assign::class;
    }

    /**
     * @param  Assign  $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->var->name instanceof Node\Identifier) {
            return [];
        }

        /** @var Node\Expr\Variable $variable */
        $variable = $node->var->var;

        $classname = $scope->getType($variable)->getReferencedClasses()[0] ?? null;

        if (! $classname) {
            return [];
        }

        if (! $this->domainResolver->has($classname)) {
            return [
                RuleErrorBuilder::message(
                    "The model '{$classname}' does not belong to any Domain. Please update the config.",
                )->build(),
            ];
        }

        if ($this->domainResolver->matches($classname, $scope->getNamespace())) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Mutating an Eloquent Model outside of its Domain is not allowed.',
            )->build(),
        ];
    }
}
