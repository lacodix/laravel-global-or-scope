---
title: Register Global Or Scopes
weight: 1
---

There are multiple ways of registering global scopes that shall use or conditions.

## addGlobalOrScopes

The most common use case is registering a global scope on booting the model. 
Therefor just add our Trait to your model and finally add the global scopes to your model.

> ATTENTION: to make our trait work, the global scopes must be registered before the
boot()-method of the base Eloquent model is called. The easy way is using 
the booting-method of your model.

```php
class Post extends Model
{
    use GlobalOrScope;

    public static function booting(): void
    {
        static::addGlobalOrScopes([Scope1::class, Scope2::class]);
    }
}
```

But you can also go with the boot method if you ensure to run parent::boot() 
after registering the global scopes.

```php
class Post extends Model
{
    use GlobalOrScope;

    public static function boot(): void
    {
        static::addGlobalOrScopes([Scope1::class, Scope2::class]);
        
        parent::boot();
    }
}
```

## addGlobalOrScope

You can also add one single scope, what doesn't make really sense. But if
you want to use a loop for registering it might be the correct way.

```php
class Post extends Model
{
    use GlobalOrScope;
    
    protected static array $mySpecialScopes = [
        Scope1::class, 
        Scope2::class,
    ];

    public static function boot(): void
    {
        foreach (static::$mySpecialScopes as $scope) {
            static::addGlobalOrScope($scope);
        }
        
        parent::boot();
    }
}
```

## Scope Types

You can use all type of scopes that are know from laravel, with and without keys.

```php
class Post extends Model
{
    use GlobalOrScope;

    public static function boot(): void
    {
        static::addGlobalOrScope(Scope1::class);
        
        static::addGlobalOrScope(new Scope1());
        
        static::addGlobalOrScope('my_scope', new Scope1());
        
        static::addGlobalOrScope('my_closure_scope', function ($query) {
            $query->where('email', 'someone@else.com');
        });
        
        parent::boot();
    }
}
```

## withGlobalOrScopes

You can also add global scopes with or condition on the fly by using the query builder.
In this case you even don't need the GlobalOrScope trait. You can just apply scopes on 
every model query:

```php
Post::query()->withGlobalOrScopes([new Scope1, new Scope2]);
```

> ATTENTION: like in laravels base functionality (withGlobalScope) you need to add initialized
scopes, not only classnames.

## Combining with normal global scopes

It is also possible to combine the global or scopes with normal global scopes.

```php
class Post extends Model
{
    use GlobalOrScope;

    public static function boot(): void
    {
        static::addGlobalScope(CommonScope::class);
        static::addGlobalOrScopes([Scope1::class, Scope2::class]);
        
        parent::boot();
    }
}
```

## Get all global or scopes

You can get all registered global scopes of a model:

```php
$post->getGlobalOrScopes();
```
