<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('farm_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->string('location_code');
            $table->string('name');

            $table->string('type')->nullable();

            $table->unsignedInteger('capacity')->nullable();

            $table->text('description')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['farm_id', 'location_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_locations');
    }
};