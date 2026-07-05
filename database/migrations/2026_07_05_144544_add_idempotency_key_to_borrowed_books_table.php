<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowed_books', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->unique()->after('identifier');
        });
    }

    public function down(): void
    {
        Schema::table('borrowed_books', function (Blueprint $table) {
            $table->dropColumn('idempotency_key');
        });
    }
};
