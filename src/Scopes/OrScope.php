<?php

namespace Lacodix\LaravelGlobalOrScope\Scopes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use InvalidArgumentException;

class OrScope implements Scope
{
    protected $scopes;
    protected $removedOrScopes = [];

    public function __construct(array $scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * @param Builder<Model> $builder
     *
     * @return Builder<Model>
     */
    public function apply(Builder $builder, Model $model): Builder
    {
        return $builder->where(function (Builder $builder) use ($model) {
            foreach ($this->scopes as $identifier => $scope) {
                if (! isset($this->scopes[$identifier])) {
                    continue;
                }

                $builder->orWhere(static function (Builder $query) use ($scope, $model): void {
                    if ($scope instanceof Closure) {
                        $scope($query);
                    } elseif ($scope instanceof Scope) {
                        $scope->apply($query, $model);
                    } else {
                        throw new InvalidArgumentException('Global scope must be an instance of Closure or Scope or be a class name of a class extending '.Scope::class);
                    }
                });
            }
            return $builder;
        });
    }

    /**
     * @param  Builder<Model>  $builder
     */
    public function extend(Builder $builder): void
    {
        $this->addWithoutGlobalOrScope($builder);
        $this->addWithoutGlobalOrScopes($builder);
        $this->addRemovedOrScopes($builder);
    }

    /**
     * @param  Builder<Model>  $builder
     */
    protected function addWithoutGlobalOrScope(Builder $builder): void
    {
        $builder->macro('withoutGlobalOrScope', function (Builder $builder, $scope) {
            if (! is_string($scope)) {
                $scope = $scope::class;
            }

            /** @phpstan-ignore-next-line */
            unset($this->scopes[$scope]);

            /** @phpstan-ignore-next-line */
            $this->removedOrScopes[] = $scope;

            return $builder;
        });
    }

    /**
     * @param  Builder<Model>  $builder
     */
    protected function addWithoutGlobalOrScopes(Builder $builder): void
    {
        $builder->macro('withoutGlobalOrScopes', function (Builder $builder, ?array $scopes = null) {
            if (! is_array($scopes)) {
                /** @phpstan-ignore-next-line */
                $scopes = array_keys($this->scopes);
            }

            foreach ($scopes as $scope) {
                $builder->withoutGlobalOrScope($scope); // @phpstan-ignore-line
            }

            return $builder;
        });
    }

    /**
     * @param  Builder<Model>  $builder
     */
    protected function addRemovedOrScopes(Builder $builder): void
    {
        /** @phpstan-ignore-next-line */
        $builder->macro('removedOrScopes', fn () => $this->removedOrScopes);
    }
}
