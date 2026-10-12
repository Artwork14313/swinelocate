<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->after('farm_id');
        });

        DB::table('farmers')
            ->whereNull('qr_token')
            ->orderBy('id')
            ->chunkById(100, function ($farmers) {
                foreach ($farmers as $farmer) {
                    DB::table('farmers')
                        ->where('id', $farmer->id)
                        ->update(['qr_token' => Str::random(48)]);
                }
            });

        Schema::table('farmers', function (Blueprint $table) {
            $table->unique('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn('qr_token');
        });
    }
};
