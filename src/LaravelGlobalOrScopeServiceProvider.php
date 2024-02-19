<?php

namespace Lacodix\LaravelGlobalOrScope;

use Illuminate\Database\Eloquent\Builder;
use Lacodix\LaravelGlobalOrScope\Scopes\OrScope;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelGlobalOrScopeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-global-or-scope');
    }

    public function boot(): void
    {
        parent::boot();

        Builder::macro(
            'withGlobalOrScopes',
            // @phpstan-ignore-next-line
            fn (array $scopes = null) => $this->withGlobalScope(md5(serialize($scopes)), new OrScope($scopes))
        );
    }
}
