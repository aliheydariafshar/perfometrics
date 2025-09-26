<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('performance_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('task_completion', 5)->default(0);
            $table->decimal('deadline_adherence', 5)->default(0);
            $table->decimal('peer_reviews', 5)->default(0);
            $table->decimal('training_completion', 5)->default(0);
            $table->decimal('final_score', 5)->default(0);
            $table->unsignedInteger('department_rank')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_scores');
    }
};
