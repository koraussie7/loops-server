<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint ) {
            ->string('minima_address', 255)
                ->nullable()
                ->unique()
                ->after('apple_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint ) {
            ->dropColumn('minima_address');
        });
    }
};
