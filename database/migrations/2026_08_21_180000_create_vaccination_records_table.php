<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccination_records', function (Blueprint $table) {

            $table->id();

            $table->foreignId('swine_id')
                ->constrained('swine')
                ->cascadeOnDelete();

            $table->foreignId('recorded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('vaccine_name');

            $table->string('vaccine_type')->nullable();

            $table->date('date_administered');

            $table->date('next_due_date')->nullable();

            $table->string('dosage')->nullable();

            $table->string('administration_route')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccination_records');
    }
};