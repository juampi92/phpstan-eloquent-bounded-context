<?php

namespace Juampi92\PHPStanEloquentBoundedContext;

use Exception;
use Illuminate\Support\Collection;
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
            ->map(function (string $file) {
                $domains = Yaml::parse(file_get_contents("{$file}.yml"));
                $this->verifyConfig($file, $domains);

                return $domains['domains'];
            })
            ->flatten(1)
            ->flatMap(function (array $domain) {
                return collect($domain['models'])
                    ->mapWithKeys(fn (string $class): array => [$class => $domain['namespace']])
                    ->all();
            });
    }

    public function matches(string $model, string $scope): bool
    {
        $domain = $this->domains->get($model);

        if (! $domain) {
            // If the model is not defined on any Domain, break.
            return false;
        }

        return str_starts_with($scope, $domain);
    }

    public function has(string $classname): bool
    {
        return $this->domains->has($classname);
    }

    private function verifyConfig(string $file, array $domainConfig): void
    {
        if (! isset($domainConfig['domains']) || ! is_array($domainConfig['domains'])) {
            throw new Exception("Error in the config file '{$file}.yml': the main entry point must be named 'domains'");
        }
    }
}
