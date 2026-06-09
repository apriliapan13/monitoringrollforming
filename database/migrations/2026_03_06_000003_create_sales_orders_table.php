<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->unique();
            $table->string('project_executive')->nullable();
            $table->string('description')->default('Channel');
            $table->string('batch')->nullable();
            $table->decimal('kg_batch', 12, 2)->nullable();
            $table->integer('length')->default(3000);
            $table->integer('quantity');
            $table->date('order_date');
            $table->date('deadline');
            $table->date('finish_date')->nullable();
            $table->enum('cell', ['1', '2', '3'])->default('3');
            $table->string('comment')->nullable();
            $table->enum('status', ['ON PROCESS', 'FINISH', 'LATE', 'ON TIME', 'PENDING'])->default('ON PROCESS');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
