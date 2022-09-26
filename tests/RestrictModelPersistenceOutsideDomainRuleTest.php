<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests;

use Juampi92\PHPStanEloquentBoundedContext\RestrictModelPersistenceOutsideDomainRule;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fakes\DomainResolverFake;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Comment;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Models\Post;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Models\User;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RestrictModelPersistenceOutsideDomainRule>
 */
class RestrictModelPersistenceOutsideDomainRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        $domainResolver = DomainResolverFake::fromMap([
            User::class => 'Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Users',
            Comment::class => 'Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts',
            Post::class => 'Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts',
        ]);

        return new RestrictModelPersistenceOutsideDomainRule($domainResolver);
    }

    public function testViolation(): void
    {
        $this->analyse([__DIR__.'/Fixtures/App/Controllers/ViolationController.php'], [
            [sprintf("Calling 'save' on '%s' outside of its Domain is not allowed.", User::class), 16],
        ]);
    }

    public function testSuccess(): void
    {
        $this->analyse([__DIR__.'/Fixtures/App/Domains/Posts/Actions/CreatePostAction.php'], []);
    }
}
