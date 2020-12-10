<?php

namespace SomeoneFamous\AuditableValues;

use Illuminate\Support\ServiceProvider;

class AuditableValuesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (!class_exists('CreateAuditableValuesTables')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_auditable_values_tables.php.stub' => database_path(
                        'migrations/' . date('Y_m_d_His') . '_create_auditable_values_tables.php'
                    ),
                ], 'migrations');
            }
        }
    }
}
