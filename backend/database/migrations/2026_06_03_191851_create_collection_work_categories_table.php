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
        Schema::create('collection_work_categories', function (Blueprint $table) {
            $table->unsignedInteger('work_id');
            $table->unsignedInteger('category_id');
            $table->primary(['work_id', 'category_id']);
            $table->foreign('work_id')->references('id')->on('collection_works')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('collection_categories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_work_categories');
    }
};
