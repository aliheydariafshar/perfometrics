<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('peer_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->index(['reviewee_id', 'reviewer_id']);
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peer_reviews');
    }
};
