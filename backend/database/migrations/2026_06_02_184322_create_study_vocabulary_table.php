<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('study_vocabulary', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('word', 100)->unique();
            $table->text('meaning');
            $table->string('part_of_speech', 30)->nullable();
            $table->string('phonetic', 100)->nullable();
            $table->string('audio_url', 500)->nullable();
            $table->text('example')->nullable();
            $table->text('example_zh')->nullable();
            $table->string('source', 255)->nullable();
            $table->text('note')->nullable();
            $table->unsignedTinyInteger('familiarity')->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->timestamp('next_review_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->boolean('auto_filled')->default(false);
            $table->timestamps();

            $table->index('next_review_at');
            $table->index('familiarity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_vocabulary');
    }
};
