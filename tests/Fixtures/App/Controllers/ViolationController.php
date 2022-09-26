<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Controllers;

use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Models\User;
use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Models\Post;

class ViolationController
{
    /**
     * @return void
     */
    public function store(User $user)
    {
        $user->email = 'new-email@domain.com';
        $user->save();

        Post::create([
            'title' => 'my title',
        ]);
    }
}
