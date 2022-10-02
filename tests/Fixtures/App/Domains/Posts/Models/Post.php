<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 */
class Post extends Model
{
    public function myMethod(string $title): void
    {
        $this->title = $title;
    }
}
