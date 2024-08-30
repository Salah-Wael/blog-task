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
        Schema::create('posts_tags', function (Blueprint $table) {
            $table->unsignedTinyInteger('tag_id')->nullable();
            $table->foreign('tag_id')->references('id')->on('tags')->nullOnDelete();

            $table->unsignedBigInteger('news_id');
            $table->foreign('post_id')->references('id')->on('news')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_tags');
    }
};
