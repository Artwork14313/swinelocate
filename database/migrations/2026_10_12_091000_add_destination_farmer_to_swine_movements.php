<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swine_movements', function (Blueprint $table) {
            $table->foreignId('to_farmer_id')
                ->nullable()
                ->after('to_location_id')
                ->constrained('farmers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('swine_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('to_farmer_id');
        });
    }
};
