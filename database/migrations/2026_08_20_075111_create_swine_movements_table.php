<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swine_movements', function (Blueprint $table) {

            $table->id();

            $table->foreignId('swine_id')
                ->constrained('swine')
                ->cascadeOnDelete();

            $table->foreignId('from_location_id')
                ->nullable()
                ->constrained('farm_locations')
                ->nullOnDelete();

            $table->foreignId('to_location_id')
                ->nullable()
                ->constrained('farm_locations')
                ->nullOnDelete();

            $table->dateTime('movement_date');

            $table->string('reason')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swine_movements');
    }
};