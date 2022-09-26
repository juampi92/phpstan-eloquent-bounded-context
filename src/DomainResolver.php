<?php

namespace Juampi92\PHPStanEloquentBoundedContext;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

class DomainResolver
{
    /** @var Collection<class-string, string> */
    protected Collection $domains;

    /**
     * @param  array<string>  $domainDefinitionFiles
     */
    public function __construct(array $domainDefinitionFiles)
    {
        $this->domains = collect($domainDefinitionFiles)
            // Normalize files to end with .yml
            ->map(fn (string $file) => Str::finish($file, '.yml'))
            // Parse the content
            ->map(function (string $file) {
                $domains = Yaml::parse(file_get_contents($file));
                $this->verifyConfig($file, $domains);

                return $domains['domains'];
            })
            // Flatten into one config
            ->flatten(1)
            // Format into Model => Domain
            ->flatMap(function (array $domain) {
                $this->verifyDomain($domain);

                $namespace = $domain['namespace'];

                return collect($domain['models'])
                    ->mapWithKeys(function (string $model) use ($namespace): array {
                        return [$model => $namespace];
                    })
                    ->all();
            });
    }

    public function matches(string $model, string $scope): bool
    {
        $domain = $this->domains->get($model) ?: $this->autoResolve($model);

        if (! $domain) {
            // If the model is not defined on any Domain, break.
            return false;
        }

        return str_starts_with($scope, $domain);
    }

    public function has(string $classname): bool
    {
        return $this->domains->has($classname) ?: (bool) $this->autoResolve($classname);
    }

    private function autoResolve(string $model): ?string
    {
        $namespace = Str::of($model)->beforeLast('\\');

        if ($namespace == $model || $namespace == 'App') {
            // No namespace or root app namespace.
            return null;
        }

        if ($namespace->startsWith('App\Models')) {
            return null;
        }

        if ($namespace->endsWith('\\Models')) {
            // If The namespace ends with the folder Models, remove that folder to get the domain.
            $namespace = $namespace->beforeLast('\\');
        }

        // Cache for next time.
        $this->domains->put($model, (string) $namespace);

        return $namespace;
    }

    /*
     * Config helpers
     */

    private function verifyConfig(string $file, array $domainConfig): void
    {
        if (! isset($domainConfig['domains']) || ! is_array($domainConfig['domains'])) {
            throw new InvalidArgumentException("Error in the config file '{$file}': the main entry point must be named 'domains'");
        }
    }

    private function verifyDomain(array $domain): void
    {
        if (! isset($domain['namespace']) || ! is_string($domain['namespace'])) {
            throw new InvalidArgumentException("Error in the config: each item must have a key 'namespace'");
        }

        if (! isset($domain['models']) || ! is_array($domain['models'])) {
            throw new InvalidArgumentException("Error in the config: each item must have a key 'models' with a list of Model classes");
        }
    }
}
