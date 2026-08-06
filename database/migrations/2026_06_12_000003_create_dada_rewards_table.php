<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dada_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profile_id')->index();
            $table->string('video_id');
            $table->string('video_title')->nullable();
            $table->unsignedInteger('watched_seconds')->default(0);
            $table->unsignedBigInteger('reward_amount')->default(0);
            $table->string('wallet_address', 255)->nullable();
            $table->string('txpowid', 255)->nullable();
            $table->string('status', 32)->default('recorded');
            $table->timestamps();

            $table->index(['profile_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dada_rewards');
    }
};
