<?php

namespace SomeoneFamous\AuditableValues\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait AuditableValues
{
    public function getValuesTableNameAttribute(): string
    {
        return Str::singular(static::getTable()) . '_values';
    }

    public function getValuesForeignKeyFieldAttribute(): string
    {
        return Str::singular(static::getTable()) . '_id';
    }

    public function getCurrentValueAttribute()
    {
        return $this->getValue();
    }

    public function getValue(?Carbon $time =  null)
    {
        $time = $time ?: Carbon::now();

        $auditableValue = DB::table($this->values_table_name)
            ->where($this->values_foreign_key_field, $this->id)
            ->where('active_from', '<=', $time)
            ->where(function($query) use ($time) {
                $query->where('active_to', '>', $time)->orWhereNull('active_to');
            })
            ->first();

        return $auditableValue ? $auditableValue->value : null;
    }

    public function setValue($value = null, ?Carbon $time = null): self
    {
        $now = Carbon::now();

        $time = $time ?: $now;

        $this->deleteValue($time);

        DB::table($this->values_table_name)->insert([
            $this->values_foreign_key_field => $this->id,
            'active_from' => $time,
            'value' => $value,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        return $this;
    }

    public function deleteValue(?Carbon $time = null): bool
    {
        $time = $time ?: Carbon::now();

        return (bool) DB::table($this->values_table_name)
            ->where($this->values_foreign_key_field, $this->id)
            ->where(function($query) use ($time) {
                $query->where('active_to', '>=', $time)->orWhereNull('active_to');
            })
            ->update(['active_to' => $time, 'updated_at' => Carbon::now()]);
    }

    public function getHistory(?Carbon $start_time = null, ?Carbon $end_time = null)
    {
        $query = DB::table($this->values_table_name)->where($this->values_foreign_key_field, $this->id);

        if ($start_time !== null) {
            $query->where(function($query) use ($start_time) {
                $query->where('active_to', '>', $start_time)->orWhereNull('active_to');
            });
        }

        if ($end_time !== null) {
            $query->where(function($query) use ($end_time) {
                $query->where('active_from', '<', $end_time)->orWhereNull('active_from');
            });
        }

        return $query->get();
    }
}
