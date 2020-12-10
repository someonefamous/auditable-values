<?php

namespace SomeoneFamous\AuditableValues\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SomeoneFamous\AuditableValues\Traits\AuditableValues;
use SomeoneFamous\AuditableValues\Database\Factories\TestModelFactory;

class TestModel extends Model
{
    use AuditableValues;
    use HasFactory;

    protected static function newFactory(): TestModelFactory
    {
        return TestModelFactory::new();
    }
}
