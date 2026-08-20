<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swine', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->foreignId('current_location_id')
                ->nullable()
                ->constrained('farm_locations')
                ->nullOnDelete();

            $table->string('tag_number')->unique();

            $table->string('name')->nullable();

            $table->string('sex');

            $table->string('breed')->nullable();

            $table->date('birth_date')->nullable();

            $table->date('acquisition_date')->nullable();

            $table->string('source')->nullable();

            $table->string('status')->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swine');
    }
};