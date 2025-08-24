<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('member_id');
            $table->uuid('book_id');

            $table->date('borrowed_at');    // tanggal dipinjam
            $table->date('returned_at')->nullable();  // tanggal kembali, nullable karena bisa belum dikembalikan

            $table->timestamps();

            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->onDelete('cascade');

            $table->foreign('book_id')
                ->references('id')
                ->on('books')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
