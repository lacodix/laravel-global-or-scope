---
title: Disable Global Or Scopes
weight: 2
---

It is possible to disable/remove the global or scopes for single queries.

## Disable one scope

If you want to disable one of your or combined global scopes, you can just use the
withoutGlobalOrScope method on the current query builder.

```php
Post::query()->withoutGlobalOrScope(Scope1::class)->get();
```

## Disable all scopes

you can also disable all global or scopes at once.

```php
Post::query()->withoutGlobalOrScopes()->get();
```

### Get the disabled scopes

if you need to know which scopes have been disabled you get it like so:

```php
Post::query()->removedOrScopes();
```

### Disable all scopes for the whole request 

If you want to disable all global scopes for the whole request, just call

```php
Post::clearGlobalOrScopes();
```
