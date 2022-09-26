<?php

namespace Juampi92\PHPStanEloquentBoundedContext;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class RestrictModelPersistenceOutsideDomainRule implements Rule
{
    /** @var array<string> */
    private const MUTABLE_METHODS = [
        // Update
        'update', 'increment', 'decrement',
        // Create / Update
        'create', 'save', 'saveQuietly', 'createQuietly',
        'updateOrCreate', 'firstOrCreate', 'upsert',
        // Mutate
        'setAttribute', 'fill',
        // Delete
        'delete', 'restore', 'forceDelete', 'deleteQuietly', 'restoreQuietly',
    ];

    private ReflectionProvider $reflectionProvider;

    protected DomainResolver $domainResolver;

    public function __construct(ReflectionProvider $reflectionProvider, DomainResolver $domainResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->domainResolver = $domainResolver;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param  MethodCall  $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Node\Identifier) {
            return [];
        }

        if (! in_array($node->name->toString(), self::MUTABLE_METHODS)) {
            // We only care about mutable eloquent methods.
            return [];
        }

        /** @var Node\Expr\Variable $variable */
        $variable = $node->var;

        $classname = $scope->getType($variable)->getReferencedClasses()[0] ?? null;

        if (! $classname) {
            return [];
        }

        $class = $this->reflectionProvider->getClass($classname);

        if (! $class->isSubclassOf(Model::class)) {
            var_dump($class->getName(), $class->getParentClassesNames(), $class->getParents(), $class->getParentClass());

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
                "Calling '{$node->name->toString()}' on '{$classname}' outside of its Domain is not allowed.",
            )->build(),
        ];
    }
}
