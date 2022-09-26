<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures;

abstract class FakeModel
{
    public function save(): self
    {
        return $this;
    }

    public static function create(array $attrs = []): void
    {
    }
}
