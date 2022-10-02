<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 */
class User extends Model
{
    public function myMethod(string $email): void
    {
        $this->email = $email;
    }
}
