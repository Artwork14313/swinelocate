<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {

            $table->id();

            $table->foreignId('swine_id')
                ->constrained('swine')
                ->cascadeOnDelete();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('record_date');

            $table->string('record_type');

            $table->text('symptoms')->nullable();

            $table->text('diagnosis')->nullable();

            $table->text('treatment')->nullable();

            $table->text('observations')->nullable();

            $table->text('veterinary_assessment')->nullable();

            $table->string('health_status')
                ->default('healthy');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};