<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('id')
                ->constrained('roles')
                ->nullOnDelete();

            $table->foreignId('farm_id')
                ->nullable()
                ->after('role_id')
                ->constrained('farms')
                ->nullOnDelete();

            $table->string('phone')
                ->nullable()
                ->after('email');

            $table->string('status')
                ->default('active')
                ->after('phone');

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['farm_id']);

            $table->dropColumn([
                'role_id',
                'farm_id',
                'phone',
                'status',
                'deleted_at',
            ]);
        });
    }
};