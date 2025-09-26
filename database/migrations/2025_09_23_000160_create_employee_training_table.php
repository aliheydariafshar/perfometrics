<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('employee_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->date('assigned_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'training_id']);
            $table->index(['user_id', 'training_id', 'completed_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_training');
    }
};
