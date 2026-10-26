<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swine_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('swine_id')->unique()->constrained('swine')->cascadeOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_contact', 50);
            $table->decimal('sale_price', 10, 2);
            $table->date('sold_at');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swine_sales');
    }
};
