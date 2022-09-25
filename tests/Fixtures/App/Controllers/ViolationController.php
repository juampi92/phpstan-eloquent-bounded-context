<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Controllers;

use Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Models\User;

class ViolationController
{
    /**
     * @return void
     */
    public function store(User $user)
    {
        $user->email = 'new-email@domain.com';
        $user->save();
    }
}
