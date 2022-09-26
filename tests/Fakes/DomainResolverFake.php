<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests\Fakes;

use Juampi92\PHPStanEloquentBoundedContext\DomainResolver;

class DomainResolverFake extends DomainResolver
{
    // @phpstan-ignore-next-line
    public function __construct()
    {
    }

    /**
     * @param  array<class-string, string>  $map
     * @param  array<string>  $ignore
     * @return $this
     */
    public static function fromMap(array $map, array $ignore = []): self
    {
        $instance = new self();
        $instance->domains = collect($map);
        $instance->ignoredNamespaces = $ignore;

        return $instance;
    }
}
