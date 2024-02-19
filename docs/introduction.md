---
title: Introduction
weight: 1
---

This package allows you to add global scopes to models combined with an or condition.
It contains additional functionality to disable some or all or-scopes on the fly.

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
