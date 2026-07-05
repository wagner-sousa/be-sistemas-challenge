<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->index('author_id');
            $table->index('active');
            $table->index(['active', 'author_id']);
        });

        Schema::table('borrowed_books', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('book_id');
            $table->index('identifier');
            $table->index('started_at');
            $table->index('ended_at');
            $table->index(['user_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['author_id']);
            $table->dropIndex(['active']);
            $table->dropIndex(['active', 'author_id']);
        });

        Schema::table('borrowed_books', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['book_id']);
            $table->dropIndex(['identifier']);
            $table->dropIndex(['started_at']);
            $table->dropIndex(['ended_at']);
            $table->dropIndex(['user_id', 'ended_at']);
        });
    }
};
