<?php

namespace Juampi92\PHPStanEloquentBoundedContext;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class RestrictModelStaticPersistenceOutsideDomainRule implements Rule
{
    /** @var array<string> */
    private const MUTABLE_METHODS = [
        // Create
        'create', 'findOrCreate', 'createQuietly', 'firstOrCreate',
        // Update
        'updateOrCreate', 'upsert',
        // Delete
        'destroy', 'truncate',
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
        return StaticCall::class;
    }

    /**
     * @param  StaticCall  $node
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

        if (! $node->class instanceof FullyQualified) {
            return [];
        }

        /** @var FullyQualified $class */
        $classname = (string) $node->class;

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
                "Calling '{$classname}::{$node->name->toString()}' outside of its Domain is not allowed."
            )->build(),
        ];
    }
}
