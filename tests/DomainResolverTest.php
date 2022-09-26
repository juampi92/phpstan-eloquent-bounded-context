<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests;

use InvalidArgumentException;
use Juampi92\PHPStanEloquentBoundedContext\DomainResolver;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Controllers\ViolationController;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Actions\CreatePostAction;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Comment;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Models\Post;
use PHPUnit\Framework\TestCase;

class DomainResolverTest extends TestCase
{
    public function testResolvesFromYmlFile(): void
    {
        // Act
        $domainResolver = new DomainResolver([
            __DIR__.'/Fixtures/App/Domains/domains',
        ], []);

        // Assert
        $this->assertTrue($domainResolver->has(Comment::class));
        $this->assertTrue($domainResolver->has('Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Models\Post'));

        $this->assertTrue(
            $domainResolver->matches(Comment::class, CreatePostAction::class),
        );
        $this->assertFalse(
            $domainResolver->matches(Comment::class, ViolationController::class),
        );
    }

    public function testResolvesFromManyYmlFiles(): void
    {
        // Act
        $domainResolver = new DomainResolver([
            __DIR__.'/Fixtures/App/Domains/Posts/comment-domain',
            __DIR__.'/Fixtures/App/Domains/Posts/post-domain',
        ], []);

        // Assert
        $this->assertTrue($domainResolver->has(Post::class));
        $this->assertTrue($domainResolver->has(Comment::class));

        $this->assertTrue(
            $domainResolver->matches(Post::class, CreatePostAction::class),
        );
        $this->assertTrue(
            $domainResolver->matches(Comment::class, CreatePostAction::class),
        );
    }

    public function testResolvesWithTrailingYmlExtension(): void
    {
        // Act
        $domainResolver = new DomainResolver([
            __DIR__.'/Fixtures/App/Domains/domains.yml',
        ], []);

        // Assert
        $this->assertTrue($domainResolver->has(Post::class));
    }

    public function testDoesNotResolveWhenIncorrect(): void
    {
        // Arrange
        $invalidConfig = __DIR__.'/Fixtures/App/Domains/invalid-config';

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            "Error in the config file '%s.yml': the main entry point must be named 'domains'",
            $invalidConfig,
        ));

        // Act
        new DomainResolver([$invalidConfig], []);
    }

    public function testItIgnoresCorrectly(): void
    {
        // Arrange
        $invalidConfig = __DIR__.'/Fixtures/App/Domains/domains';

        // Act
        $domainResolver = new DomainResolver([$invalidConfig], ['Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Controllers']);

        // Assert
        $this->assertTrue(
            $domainResolver->matches(Comment::class, ViolationController::class),
            'The ViolationController should be ignored'
        );
    }

    public function testItDoesAutoResolveModelsInsideDomains(): void
    {
        // Act
        $domainResolver = new DomainResolver([], []);

        // Assert
        // Valid
        $this->assertTrue($domainResolver->has('App\Domains\MyModel'), 'Should resolve for Domain\root models');
        $this->assertTrue($domainResolver->has('App\Domains\Models\MyModel'), 'Should resolve for Domain\Models models');

        // False
        $this->assertFalse($domainResolver->has('App\Models\MyModel'), 'Should not resolve for App\Models');
        $this->assertFalse($domainResolver->has('App\MyModel'), 'Should not resolve for root models');
    }

    /**
     * @dataProvider modelDomainProvider
     */
    public function testItDoesAutoResolveTheDomainCorrectly(bool $expected, string $model, string $domainClass): void
    {
        $domainResolver = new DomainResolver([], []);

        $this->assertEquals(
            $expected, $domainResolver->matches($model, $domainClass)
        );
    }

    public function modelDomainProvider(): array
    {
        return [
            'Repository' => [
                'expected' => true,
                'model' => 'App\Domains\Posts\Models\Post',
                'domainClass' => 'App\Domains\Posts\Repositories',
            ],
            'Root Domain' => [
                'expected' => true,
                'model' => 'App\Domains\Posts\Models\Post',
                'domainClass' => 'App\Domains\Posts',
            ],
            'Different Domain' => [
                'expected' => false,
                'model' => 'App\Domains\Posts\Models\Post',
                'domainClass' => 'App\Domains\Users\Repositories',
            ],
        ];
    }
}
