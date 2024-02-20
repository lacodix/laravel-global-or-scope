<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Lacodix\LaravelGlobalOrScope\Traits\GlobalOrScope;

test('GlobalOrScopeIsApplied', function () {
    $model = new EloquentGlobalOrScopesTestModel;
    $query = $model->newQuery();
    $this->assertSame('select * from "table" where (("active" = ?) or ("confirmed" = ?))', $query->toSql());
    $this->assertEquals([1,0], $query->getBindings());
});

test('GlobalOrScopeCanBeRemoved', function () {
    $model = new EloquentGlobalOrScopesTestModel;
    $query = $model->newQuery()->withoutGlobalOrScope(ActiveScope::class);
    $this->assertSame('select * from "table" where (("confirmed" = ?))', $query->toSql());
    $this->assertEquals([0], $query->getBindings());
});

test('QueryGlobalOrScopeIsApplied', function () {
    $model = new EloquentQueryGlobalOrScopesTestModel;
    $query = $model->newQuery()->withGlobalOrScopes([new ActiveScope, new ConfirmedScope]);
    $this->assertSame('select * from "table" where (("active" = ?) or ("confirmed" = ?))', $query->toSql());
    $this->assertEquals([1,0], $query->getBindings());
});

test('ClassNameGlobalOrScopeIsApplied', function () {
    $model = new EloquentClassNameGlobalOrScopesTestModel;
    $query = $model->newQuery();
    $this->assertSame('select * from "table" where (("active" = ?) or ("confirmed" = ?))', $query->toSql());
    $this->assertEquals([1,0], $query->getBindings());
});

test('ClosureGlobalOrScopeIsApplied', function () {
    $model = new EloquentClosureGlobalOrScopesTestModel;
    $query = $model->newQuery();
    $this->assertSame('select * from "table" where (("active" = ?) or ("confirmed" = ?))', $query->toSql());
    $this->assertEquals([1,0], $query->getBindings());
});

test('GlobalOrScopesCanBeRegisteredViaArray', function () {
    $model = new EloquentGlobalOrScopesArrayTestModel;
    $query = $model->newQuery();
    $this->assertSame('select * from "table" where (("active" = ?) or ("confirmed" = ?))', $query->toSql());
    $this->assertEquals([1,0], $query->getBindings());
});

test('ClosureGlobalOrScopeCanBeRemoved', function () {
    $model = new EloquentClosureGlobalOrScopesTestModel;
    $query = $model->newQuery()->withoutGlobalOrScope('active_scope');
    $this->assertSame('select * from "table" where (("confirmed" = ?))', $query->toSql());
    $this->assertEquals([0], $query->getBindings());
});

test('GlobalOrScopeCanBeRemovedAfterTheQueryIsExecuted', function () {
    $model = new EloquentClosureGlobalOrScopesTestModel;
    $query = $model->newQuery();
    $this->assertSame('select * from "table" where (("active" = ?) or ("confirmed" = ?))', $query->toSql());
    $this->assertEquals([1,0], $query->getBindings());

    $query->withoutGlobalOrScope('active_scope');
    $this->assertSame('select * from "table" where (("confirmed" = ?))', $query->toSql());
    $this->assertEquals([0], $query->getBindings());
});

test('AllGlobalScopesCanBeRemoved', function () {
    $model = new EloquentClosureGlobalOrScopesTestModel;
    $query = $model->newQuery()->withoutGlobalOrScopes();
    $this->assertSame('select * from "table"', $query->toSql());
    $this->assertEquals([], $query->getBindings());

    $query = EloquentClosureGlobalOrScopesTestModel::withoutGlobalOrScopes();
    $this->assertSame('select * from "table"', $query->toSql());
    $this->assertEquals([], $query->getBindings());
});

test('GlobalOrScopesWithOrWhereConditionsAreNested', function () {
    $model = new EloquentClosureGlobalOrScopesWithAndTestModel;

    $query = $model->newQuery();
    $this->assertSame('select * from "table" where (("email" = ? and "email_confirmed" = ?) or ("email" = ?) or ("active" = ?) or ("confirmed" = ?))', $query->toSql());
    $this->assertEquals(['dominik@gmail.com', 1,  'someone@else.com', 1 , 0], $query->getBindings());

    $query = $model->newQuery()->where('col1', 'val1')->orWhere('col2', 'val2');
    $this->assertSame('select * from "table" where ("col1" = ? or "col2" = ?) and (("email" = ? and "email_confirmed" = ?) or ("email" = ?) or ("active" = ?) or ("confirmed" = ?))', $query->toSql());
    $this->assertEquals(['val1', 'val2', 'dominik@gmail.com', 1,  'someone@else.com', 1 , 0], $query->getBindings());
});

test('RegularScopesWithOrWhereConditionsAreNested', function () {
    $query = EloquentClosureGlobalOrScopesTestModel::withoutGlobalOrScopes()->where('foo', 'foo')->orWhere('bar', 'bar')->approved();

    $this->assertSame('select * from "table" where ("foo" = ? or "bar" = ?) and ("approved" = ? or "should_approve" = ?)', $query->toSql());
    $this->assertEquals(['foo', 'bar', 1, 0], $query->getBindings());
});

test('ScopesStartingWithOrBooleanArePreserved', function () {
    $query = EloquentClosureGlobalOrScopesTestModel::withoutGlobalOrScopes()->where('foo', 'foo')->orWhere('bar', 'bar')->orApproved();

    $this->assertSame('select * from "table" where ("foo" = ? or "bar" = ?) or ("approved" = ? or "should_approve" = ?)', $query->toSql());
    $this->assertEquals(['foo', 'bar', 1, 0], $query->getBindings());
});

test('HasQueryWhereBothModelsHaveGlobalOrScopes', function () {
    $query = EloquentGlobalOrScopesWithRelationModel::has('related')->where('bar', 'baz');

    $subQuery = 'select * from "table" where "table2"."id" = "table"."related_id" and "foo" = ? and (("active" = ?) or ("confirmed" = ?))';
    $mainQuery = 'select * from "table2" where exists ('.$subQuery.') and "bar" = ? and (("active" = ?) or ("confirmed" = ?))';

    $this->assertEquals($mainQuery, $query->toSql());
    $this->assertEquals(['bar', 1, 0, 'baz', 1, 0], $query->getBindings());
});

class EloquentQueryGlobalOrScopesTestModel extends Model
{
    use GlobalOrScope;

    protected $table = 'table';

    public static function booting(): void
    {
        static::clearGlobalOrScopes();
    }
}

class EloquentGlobalOrScopesTestModel extends Model
{
    use GlobalOrScope;

    protected $table = 'table';

    public static function booting(): void
    {
        static::clearGlobalOrScopes();
    }

    public static function boot()
    {
        static::addGlobalOrScopes([new ActiveScope, new ConfirmedScope]);

        parent::boot();
    }
}

class EloquentClassNameGlobalOrScopesTestModel extends Model
{
    use GlobalOrScope;

    protected $table = 'table';

    public static function booting(): void
    {
        static::clearGlobalOrScopes();
    }

    public static function boot()
    {
        static::addGlobalOrScopes([ActiveScope::class, ConfirmedScope::class]);

        parent::boot();
    }
}

class EloquentClosureGlobalOrScopesTestModel extends Model
{
    use GlobalOrScope;

    protected $table = 'table';

    public static function booting(): void
    {
        static::clearGlobalOrScopes();
    }

    public static function boot(): void
    {
        static::addGlobalOrScope('active_scope', function ($query) {
            $query->where('active', 1);
        });

        static::addGlobalOrScope(function ($query) {
            $query->where('confirmed', 0);
        });

        parent::boot();
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', 1)->orWhere('should_approve', 0);
    }

    public function scopeOrApproved($query)
    {
        return $query->orWhere('approved', 1)->orWhere('should_approve', 0);
    }
}

class EloquentGlobalOrScopesArrayTestModel extends Model
{
    use GlobalOrScope;

    protected $table = 'table';

    public static function booting(): void
    {
        static::clearGlobalOrScopes();
    }

    public static function boot()
    {
        static::addGlobalOrScopes([
            'active_scope' => new ActiveScope,
            fn ($query) => $query->where('confirmed', 0),
        ]);

        parent::boot();
    }
}

class EloquentClosureGlobalOrScopesWithAndTestModel extends EloquentClosureGlobalOrScopesTestModel
{
    public static function boot(): void
    {
        static::addGlobalOrScope('and_scope', function ($query) {
            $query->where('email', 'dominik@gmail.com')->where('email_confirmed', 1);
        });

        static::addGlobalOrScope(function ($query) {
            $query->where('email', 'someone@else.com');
        });

        parent::boot();
    }
}

class EloquentGlobalOrScopesWithRelationModel extends EloquentClosureGlobalOrScopesTestModel
{
    protected $table = 'table2';

    public function related()
    {
        return $this->hasMany(EloquentGlobalOrScopesTestModel::class, 'related_id')->where('foo', 'bar');
    }
}

class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where('active', 1);
    }
}

class ConfirmedScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where('confirmed', 0);
    }
}
