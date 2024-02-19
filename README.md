# laravel-global-or-scope

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lacodix/laravel-global-or-scope.svg?style=flat-square)](https://packagist.org/packages/lacodix/laravel-global-or-scope)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lacodix/laravel-global-or-scope/test.yaml?branch=master&label=tests&style=flat-square)](https://github.com/lacodix/laravel-global-or-scope/actions?query=workflow%3Atest+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lacodix/laravel-global-or-scope/style.yaml?branch=master&label=code%20style&style=flat-square)](https://github.com/lacodix/laravel-global-or-scope/actions?query=workflow%3Astyle+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/lacodix/laravel-global-or-scope.svg?style=flat-square)](https://packagist.org/packages/lacodix/laravel-global-or-scope)

This package allows you to add global scopes to models combined with an or condition.
It contains additional functionality to disable some or all or-scopes on the fly.

## Documentation

You can find the entire documentation for this package on [our documentation site](https://www.lacodix.de/docs/laravel-global-or-scope)

## Installation

```bash
composer require lacodix/laravel-global-or-scope
```

## Basic Usage

Just add the trait to your eloquent model and then you can use the addGlobalOrScopes method when booting.

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Lacodix\LaravelGlobalOrScope\Traits\GlobalOrScope;

class Post extends Model
{
    use GlobalOrScope;

    public static function booting(): void
    {
        static::addGlobalOrScopes([Scope1::class, Scope2::class]);
    }
}

class Scope1 implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->whereNull('col1')->where('col2', 1);
    }
}

class Scope2 implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where('col3', 2);
    }
}
...
Post::query()->where('user_id', 1000)->get();
```

This results in running the following SQL Query
```sql
select * from "posts" where "user_id" = 1000 and (("col1" is null and "col2" = 1) or ("col3" = 2))
```

For temporary disabling you can use
```php
Post::query()->withoutGlobalOrScopes()->where('user_id', 1000)->get();
```
what results in a simple
```sql
select * from "posts" where "user_id" = 1000
```

## Testing

```bash
composer test
```

## Contributing

Please run the following commands and solve potential problems before committing
and think about adding tests for new functionality.

```bash
composer rector:test
composer insights
composer csfixer:test
composer phpstan:test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [lacodix](https://github.com/lacodix)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
