<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineSetting extends Model
{
    protected $fillable = [
        'product_name',
        'machine_name',
        'shift_count',
        'work_time_sec',
        'allowance_time_sec',
        'changeover_time_sec',
        'cycle_time_sec',
        'uptime_percentage',
        'man_power',
        'cost_man_hour',
    ];

    public function getAvailableWorkTimeAttribute(): float
    {
        return $this->work_time_sec - $this->allowance_time_sec - $this->changeover_time_sec;
    }

    public function getCapacityPerShiftAttribute(): float
    {
        if ($this->cycle_time_sec <= 0) return 0;
        return $this->available_work_time / $this->cycle_time_sec;
    }

    public function getDailyCapacityAttribute(): float
    {
        return $this->capacity_per_shift * $this->shift_count;
    }

    public function getUptimeDecimalAttribute(): float
    {
        return $this->uptime_percentage / 100;
    }
}
