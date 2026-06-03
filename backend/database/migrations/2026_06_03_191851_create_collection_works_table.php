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
        Schema::create('collection_works', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->enum('type', ['anime', 'manga']);
            $table->string('title', 255);
            $table->string('title_original', 255)->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->enum('status', ['watching', 'completed', 'plan', 'on_hold', 'dropped'])->default('plan');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->unsignedSmallInteger('release_year')->nullable();
            $table->enum('release_season', ['winter', 'spring', 'summer', 'autumn'])->nullable();
            $table->string('media_type', 30)->nullable();
            $table->string('source_type', 30)->nullable();
            $table->unsignedSmallInteger('episodes_total')->nullable();
            $table->unsignedSmallInteger('episodes_watched')->default(0);
            $table->unsignedSmallInteger('volumes_total')->nullable();
            $table->unsignedSmallInteger('volumes_read')->default(0);
            $table->string('author', 100)->nullable();
            $table->string('studio', 100)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('release_year');
            $table->index('is_favorite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_works');
    }
};
