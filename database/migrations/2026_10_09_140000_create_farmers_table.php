<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('contact_number', 30);
            $table->string('email')->nullable();
            $table->text('address');
            $table->timestamps();
        });

        Schema::table('swine', function (Blueprint $table) {
            $table->foreignId('farmer_id')
                ->nullable()
                ->after('farm_id')
                ->constrained()
                ->nullOnDelete();

            $table->uuid('local_id')
                ->nullable()
                ->unique()
                ->after('farmer_id');
        });
    }

    public function down(): void
    {
        Schema::table('swine', function (Blueprint $table) {
            $table->dropUnique(['local_id']);
            $table->dropColumn('local_id');
            $table->dropConstrainedForeignId('farmer_id');
        });

        Schema::dropIfExists('farmers');
    }
};
