<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swine', function (Blueprint $table) {
            $table->boolean('is_for_sale')->default(false);
            $table->decimal('asking_price', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('swine', function (Blueprint $table) {
            $table->dropColumn(['is_for_sale', 'asking_price']);
        });
    }
};
