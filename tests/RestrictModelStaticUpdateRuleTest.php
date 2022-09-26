<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests;

use Juampi92\PHPStanEloquentBoundedContext\RestrictModelStaticUpdateRule;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fakes\DomainResolverFake;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Comment;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Models\Post;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Models\User;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RestrictModelStaticUpdateRuleTest>
 */
class RestrictModelStaticUpdateRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        $domainResolver = DomainResolverFake::fromMap([
            Comment::class => 'Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts',
            Post::class => 'Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts',
        ]);

        return new RestrictModelStaticUpdateRule(
            $this->createReflectionProvider(),
            $domainResolver,
        );
    }

    public function testViolation(): void
    {
        $this->analyse([__DIR__.'/Fixtures/App/Controllers/ViolationController.php'], [
            [sprintf("Calling '%s::create' outside of its Domain is not allowed.", Post::class), 18],
        ]);
    }

    public function testSuccess(): void
    {
        $this->analyse([__DIR__.'/Fixtures/App/Domains/Posts/Actions/CreatePostAction.php'], []);
    }
}
