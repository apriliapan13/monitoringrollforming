<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_settings', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->default('Straight Single Solid Channel 41x41/41x21xL3000');
            $table->string('machine_name')->default('Roll Forming 3');
            $table->integer('shift_count')->default(1);
            $table->integer('work_time_sec')->default(28800);
            $table->integer('allowance_time_sec')->default(3600);
            $table->integer('changeover_time_sec')->default(4320);
            $table->integer('cycle_time_sec')->default(67);
            $table->decimal('uptime_percentage', 5, 2)->default(72.50);
            $table->integer('man_power')->default(2);
            $table->decimal('cost_man_hour', 12, 2)->default(76267);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_settings');
    }
};
