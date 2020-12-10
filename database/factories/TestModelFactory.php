<?php

namespace SomeoneFamous\AuditableValues\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SomeoneFamous\AuditableValues\Tests\Models\TestModel;

class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->name,
        ];
    }
}
