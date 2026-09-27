<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Huduma Champions fraud signals (PDF §29): one row per user / IP / device per day,
 * written by the RecordChampionSignal middleware and shown as risk flags in admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('champion_user_signals')) return;

        Schema::create('champion_user_signals', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('ip', 45)->nullable();
            $t->string('device_fp', 64)->nullable();
            $t->string('user_agent')->nullable();
            $t->date('day');
            $t->timestamp('created_at')->nullable();
            $t->unique(['user_id', 'ip', 'device_fp', 'day'], 'champion_user_signals_unique');
            $t->index('ip', 'champion_user_signals_ip_index');
            $t->index('device_fp', 'champion_user_signals_fp_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('champion_user_signals');
    }
};
