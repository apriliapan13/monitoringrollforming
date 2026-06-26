<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrder extends Model
{
    protected $fillable = [
        'so_number',
        'project_executive',
        'description',
        'batch',
        'kg_batch',
        'size',
        'length',
        'quantity',
        'order_date',
        'deadline',
        'finish_date',
        'cell',
        'comment',
        'status',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'deadline' => 'date',
        'finish_date' => 'date',
        'kg_batch' => 'decimal:2',
        'size' => 'string',
    ];

    public function dailyTargets(): HasMany
    {
        return $this->hasMany(DailyTarget::class);
    }

    public function actualProductions(): HasMany
    {
        return $this->hasMany(ActualProduction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalActualAttribute(): int
    {
        return $this->actualProductions()->sum('actual_qty');
    }

    public function getTotalTargetAttribute(): int
    {
        return $this->dailyTargets()->sum('target_qty');
    }

    public function getRemainingQtyAttribute(): int
    {
        return max(0, $this->quantity - $this->total_actual);
    }

    public function getAchievementPercentageAttribute(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }

        return round(($this->total_actual / $this->quantity) * 100, 1);
    }

    public function getLeadtimeDaysAttribute(): int
    {
        if (!$this->order_date || !$this->deadline) {
            return 0;
        }

        $startDate = $this->order_date->copy();

        // SO yang dibuat jam 13:00 atau setelahnya dihitung mulai hari berikutnya
        if ($this->created_at && $this->created_at->hour >= 13) {
            $startDate->addDay();
        }

        // Minimal 1 hari, dan hari mulai ikut dihitung
        return max(1, $startDate->diffInDays($this->deadline) + 1);
    }

    public function getDaysStatusAttribute(): int
    {
        if (!$this->finish_date || !$this->deadline) {
            return 0;
        }

        return $this->deadline->diffInDays($this->finish_date, false);
    }

    public function getEstimatedDurationAttribute(): float
    {
        $machine = MachineSetting::first();

        if (!$machine || $machine->daily_capacity <= 0) {
            return 0;
        }

        return ceil($this->quantity / $machine->daily_capacity);
    }
}