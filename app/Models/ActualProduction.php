<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActualProduction extends Model
{
    protected $fillable = [
        'sales_order_id',
        'production_date',
        'actual_qty',
        'product_type',
        'user_id',
    ];

    protected $casts = [
        'production_date' => 'date',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
