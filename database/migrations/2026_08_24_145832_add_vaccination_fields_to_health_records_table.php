<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->string('vaccine_name')->nullable()->after('record_type');
            $table->string('dose')->nullable()->after('vaccine_name');
            $table->string('batch_number')->nullable()->after('dose');
            $table->date('next_due_date')->nullable()->after('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn([
                'vaccine_name',
                'dose',
                'batch_number',
                'next_due_date',
            ]);
        });
    }
};
