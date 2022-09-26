# PHPStan Extension: Eloquent Bounded Context

A set of PHPStan rules to make sure your models are **only being mutated from within their Domains**.
<p align="center">
  <p align="center">
    <a href="https://packagist.org/packages/juampi92/phpstan-eloquent-bounded-context"><img src="https://img.shields.io/packagist/v/juampi92/phpstan-eloquent-bounded-context.svg?style=flat-square" alt="Latest Version on Packagist"></a>
    <a href="https://github.com/juampi92/phpstan-eloquent-bounded-context/actions?query=workflow%3Arun-tests+branch%3Amain"><img src="https://img.shields.io/github/workflow/status/juampi92/phpstan-eloquent-bounded-context/run-tests?label=tests" alt="GitHub Tests Action Status"></a>
    <a href="https://packagist.org/packages/juampi92/phpstan-eloquent-bounded-context"><img src="https://img.shields.io/packagist/dt/juampi92/phpstan-eloquent-bounded-context.svg?style=flat-square" alt="Total Downloads"></a>
  </p>
</p>

## Description

These rules will detect when your Eloquent Model is being mutated outside of its Domain. The moment the Model leaves the Domain namespace, it becomes read-only.

Let's assume the following structure:
```
📁 app
├─ 📁 Http
│  └─ 📁 Controllers
│     └─ 📃 PostController.php
└─ 📁 Domains
   └─ 📁 Posts
      └─ 📁 Actions
         └─ 📃 CreatePostAction.php
      └─ 📁 Models
         └─ 📃 Post.php
         └─ 📃 Comment.php
```

If `app/Http/Controllers/PostController.php` has the following method:

```php
public function store(Request $request)
{
    $post = new Post($request->validated());
    $post->save();
}
```

Will fail saying:

```
 ---------------------------------------------------------------------------
  app/Http/Controllers/PostController.php
 ---------------------------------------------------------------------------
  Calling 'save' on 'App\Models\Post' outside of its Domain is not allowed.
 ---------------------------------------------------------------------------
```

This package will detect mutations:

```
$post->title = 'My title';
```

As well as methods that persistent the data, like `save`, `update`, `delete`, etc.
And also static methods like `::create()`, `::updateOrCreate()`, etc.

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```bash
composer require --dev juampi92/phpstan-eloquent-bounded-context
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include extension.neon in your project's PHPStan config:

```neon
includes:
    - vendor/juampi92/phpstan-eloquent-bounded-context/extension.neon
```
</details>

## Configuration

### No configuration

If your models are placed inside their Domain folder, the package will know they are not using Laravel's default folders, and assume the domain is `App/Domains/Posts`. Any class inside that domain is allowed to mutate an eloquent model. 

```
📁 app
├─ 📁 Domains
│  └─ 📁 Posts
│  |  └─ 📁 Actions
│  |  └─ 📁 Repositories
│  |  └─ 📁 Models ✅
│  |     └─ 📃 Post.php
│  |     └─ 📃 Comment.php
│  └─ 📁 Users
│     └─  ...
└── ...
```

### Manual configuration

If your models are placed inside `App\Models`, then you will have to configure your domain manually.
To do so, you must first create a configuration file that holds the information about your models and domains:

```yml
domains:
  -
    namespace: App\Domain\Posts
    models:
      - App\Models\Post
      - App\Models\Comment
  -
    namespace: App\Domain\Users
    models:
      - App\Models\User
      - App\Models\UserProfile
```

And after you must reference this file inside the `phpstan.neon.dist` config:

```neon
parameters:
	domainDefinitionFiles:
	    - app/Domain/domains.yml
```

## Testing

```bash
./vendor/bin/phpunit
```

## Credits

- [Juan Pablo Barreto](https://github.com/juampi92)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
