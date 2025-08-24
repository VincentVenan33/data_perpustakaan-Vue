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
        Schema::create('books', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->uuid('category_id');
            $table->string('published_at')->nullable();
            $table->string('is_available')->default(true);
            $table->json('details')->nullable();
            $table->string('file_path')->nullable(); // lokasi file PDF
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
        Schema::table('books', function (Blueprint $table) {
        $table->dropColumn('category_name_snapshot');
    });
    }
};
