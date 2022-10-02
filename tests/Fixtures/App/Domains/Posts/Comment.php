<?php

namespace Juampi92\PHPStanEloquentBoundedContext\Tests\Fixtures\App\Domains\Posts;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $content
 */
class Comment extends Model
{
    public function myMethod(string $content): void
    {
        $this->content = $content;
    }
}
