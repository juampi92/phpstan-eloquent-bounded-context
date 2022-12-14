<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Actions;

use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Comment;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Models\Post;

final class CreatePostAction
{
    public function execute(string $title): void
    {
        $post = new Post();
        $post->title = $title;

        $post->save();

        $comment = new Comment();
        $comment->content = 'My comment';
        $comment->save();

        Post::create([
            'title' => 'my title',
        ]);
    }
}
