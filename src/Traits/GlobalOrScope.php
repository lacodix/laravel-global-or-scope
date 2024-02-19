<?php

namespace Lacodix\LaravelGlobalOrScope\Traits;

use Closure;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lacodix\LaravelGlobalOrScope\Scopes\OrScope;

trait GlobalOrScope
{
    protected static array $globalOrScopes = [];

    public static function bootGlobalOrScope(): void
    {
        $scopesToApply = Arr::get(static::$globalOrScopes, static::class, []);

        if (count($scopesToApply) > 0) {
            static::addGlobalScope(new OrScope($scopesToApply));
        }
    }

    public static function clearGlobalOrScope(): void
    {
        static::$globalOrScopes = [];
    }

    public static function addGlobalOrScope(Scope|Closure|string $scope, Scope|Closure|null $implementation = null): Scope|Closure
    {
        if (is_string($scope) && ($implementation instanceof Closure || $implementation instanceof Scope)) {
            return static::$globalOrScopes[static::class][$scope] = $implementation;
        }
        if ($scope instanceof Closure) {
            return static::$globalOrScopes[static::class][spl_object_hash($scope)] = $scope;
        }
        if ($scope instanceof Scope) {
            return static::$globalOrScopes[static::class][$scope::class] = $scope;
        }
        if (is_string($scope) && class_exists($scope) && is_subclass_of($scope, Scope::class)) {
            return static::$globalOrScopes[static::class][$scope] = new $scope();
        }

        throw new InvalidArgumentException('Global scope must be an instance of Closure or Scope or be a class name of a class extending '.Scope::class);
    }

    public static function addGlobalOrScopes(array $scopes): void
    {
        foreach ($scopes as $key => $scope) {
            if (is_string($key)) {
                static::addGlobalOrScope($key, $scope);
            } else {
                static::addGlobalOrScope($scope);
            }
        }
    }

    public function getGlobalOrScopes()
    {
        return Arr::get(static::$globalOrScopes, static::class, []);
    }
}
