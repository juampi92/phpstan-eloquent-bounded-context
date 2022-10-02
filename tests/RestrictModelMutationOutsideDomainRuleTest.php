<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests;

use Juampi92\PHPStanEloquentBoundedContext\RestrictModelMutationOutsideDomainRule;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fakes\DomainResolverFake;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Comment;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Models\Post;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Models\User;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RestrictModelMutationOutsideDomainRule>
 */
class RestrictModelMutationOutsideDomainRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        $domainResolver = DomainResolverFake::fromMap([
            User::class => 'Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Users',
            Comment::class => 'Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts',
            Post::class => 'Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts',
        ]);

        return new RestrictModelMutationOutsideDomainRule(
            $this->createReflectionProvider(),
            $domainResolver,
        );
    }

    public function testViolation(): void
    {
        $this->analyse([__DIR__.'/Fixtures/App/Controllers/ViolationController.php'], [
            ['Mutating an Eloquent Model outside of its Domain is not allowed.', 15],
        ]);
    }

    public function testSuccess(): void
    {
        $this->analyse([__DIR__.'/Fixtures/App/Domains/Posts/Actions/CreatePostAction.php'], []);
        $this->analyse([__DIR__.'/Fixtures/App/Domains/Posts/Models/Post.php'], []);
        $this->analyse([__DIR__.'/Fixtures/App/Domains/Posts/Comment.php'], []);
        $this->analyse([__DIR__.'/Fixtures/App/Models/User.php'], []);
    }
}
