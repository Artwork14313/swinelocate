<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swine_movements', function (Blueprint $table) {
            $table->string('status')
                ->default('completed')
                ->after('notes');

            $table->string('conflict_resolution')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('swine_movements', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'conflict_resolution',
            ]);
        });
    }
};