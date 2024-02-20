---
title: Advanced Usage
weight: 3
---

Finally you can use our internal OrScope directly. This can be useful for all cases where
you already have multiple scope classes that you want to combine with an or.

```php
use Lacodix\LaravelGlobalOrScope\Scopes\OrScope;

$orScope = new OrScope([new Scope1()), new Scope2())])

$query->withGlobalScope('or_scope', $orScope);
```

This is exactly what our `withGlobalOrScopes` method on the query builder does. But with that in mind
you can even apply more complex scope combinations like this:

```php
$orScope1 = new OrScope([new Scope1()), new Scope2())])
$orScope2 = new OrScope([new Scope3()), new Scope4())])

$query->withGlobalScope('or_scope1', $orScope1);
$query->withGlobalScope('or_scope2', $orScope2);
```

This will result in a query applying
```sql 
(SCOPE1 or SCOPE2) and (SCOPE3 or SCOPE4)
```

### Scope exchange

Sometimes you already have applied one Scope to your model on the classic way:
But for some cases in your application you need this scope combined with another by or condition.
Just do this:

```php 
class Post extends Model
{
    public static function boot(): void
    {
        static::addGlobalScope(Scope1::class);
        
        parent::boot();
    }
}
```
First we remove the solo Scope1::class and re add it in the second step, combined with a new one via OrScope.
```php
    Post::query()
        ->withoutGlobalScope(Scope1::class) 
        ->withGlobalOrScopes([new Scope1(), new Scope2()]);
```

