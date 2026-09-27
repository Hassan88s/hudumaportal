<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Huduma Champions — the monthly gamification program.
 * Safe on servers where _deploy/champions_schema.sql was already run:
 * every table is created only when missing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('champion_seasons')) {
            Schema::create('champion_seasons', function (Blueprint $t) {
                $t->id();
                $t->char('season_key', 7)->unique()->comment('YYYY-MM');
                $t->dateTime('starts_at');
                $t->dateTime('ends_at');
                $t->enum('status', ['open', 'auditing', 'finalized', 'announced'])->default('open');
                $t->timestamp('finalized_at')->nullable();
                $t->timestamp('announced_at')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('champion_points')) {
            Schema::create('champion_points', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->enum('league', ['provider', 'client']);
                $t->char('season_key', 7);
                $t->string('rule_key', 64);
                $t->string('cap_group', 32)->nullable();
                $t->integer('points');
                $t->enum('status', ['pending', 'confirmed', 'reversed'])->default('confirmed');
                $t->string('source_type', 32)->nullable();
                $t->unsignedBigInteger('source_id')->nullable();
                $t->unsignedBigInteger('counterparty_id')->nullable();
                $t->string('reason')->nullable();
                $t->string('idempotency_key', 160)->unique();
                $t->timestamp('confirm_after')->nullable();
                $t->timestamp('confirmed_at')->nullable();
                $t->timestamp('reversed_at')->nullable();
                $t->string('reversal_reason')->nullable();
                $t->unsignedBigInteger('admin_id')->nullable();
                $t->timestamps();
                $t->index(['season_key', 'league', 'status', 'user_id'], 'champion_points_board_index');
                $t->index(['user_id', 'season_key'], 'champion_points_user_index');
                $t->index(['source_type', 'source_id'], 'champion_points_source_index');
                $t->index(['status', 'confirm_after'], 'champion_points_confirm_index');
            });
        }

        if (!Schema::hasTable('champion_missions')) {
            Schema::create('champion_missions', function (Blueprint $t) {
                $t->id();
                $t->enum('league', ['provider', 'client']);
                $t->enum('type', ['mission', 'demand_bonus'])->default('mission');
                $t->string('mission_key', 64);
                $t->string('title', 160);
                $t->string('description')->nullable();
                $t->integer('target')->default(1);
                $t->integer('reward_hp')->default(0);
                $t->integer('bonus_percent')->nullable();
                $t->unsignedBigInteger('city_id')->nullable();
                $t->unsignedBigInteger('category_id')->nullable();
                $t->char('season_key', 7)->nullable();
                $t->boolean('is_active')->default(true);
                $t->timestamps();
                $t->index(['league', 'is_active', 'season_key'], 'champion_missions_active_index');
            });
        }

        if (!Schema::hasTable('champion_mission_progress')) {
            Schema::create('champion_mission_progress', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('mission_id');
                $t->unsignedBigInteger('user_id');
                $t->char('season_key', 7);
                $t->integer('progress')->default(0);
                $t->timestamp('completed_at')->nullable();
                $t->timestamps();
                $t->unique(['mission_id', 'user_id', 'season_key'], 'champion_mission_progress_unique');
            });
        }

        if (!Schema::hasTable('champion_winners')) {
            Schema::create('champion_winners', function (Blueprint $t) {
                $t->id();
                $t->char('season_key', 7);
                $t->enum('league', ['provider', 'client']);
                $t->unsignedTinyInteger('rank');
                $t->unsignedBigInteger('user_id');
                $t->integer('final_hp');
                $t->enum('reward_type', ['cash', 'credit']);
                $t->decimal('reward_amount', 12, 2)->default(0);
                $t->string('benefits')->nullable();
                $t->enum('status', ['provisional', 'approved', 'disqualified', 'paid'])->default('provisional');
                $t->text('audit_notes')->nullable();
                $t->unsignedBigInteger('approved_by')->nullable();
                $t->timestamp('approved_at')->nullable();
                $t->timestamp('paid_at')->nullable();
                $t->timestamps();
                $t->unique(['season_key', 'league', 'rank'], 'champion_winners_slot_unique');
                $t->index('user_id', 'champion_winners_user_index');
            });
        }

        if (!Schema::hasTable('champion_badges')) {
            Schema::create('champion_badges', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->string('badge_key', 64);
                $t->string('label', 120);
                $t->char('season_key', 7)->default('');
                $t->timestamp('awarded_at')->nullable();
                $t->timestamps();
                $t->unique(['user_id', 'badge_key', 'season_key'], 'champion_badges_unique');
            });
        }

        if (!Schema::hasTable('champion_disqualifications')) {
            Schema::create('champion_disqualifications', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->char('season_key', 7);
                $t->enum('league', ['provider', 'client']);
                $t->string('reason');
                $t->unsignedBigInteger('admin_id')->nullable();
                $t->timestamps();
                $t->unique(['user_id', 'season_key', 'league'], 'champion_dq_unique');
            });
        }

        // Program settings — only added when missing, never overwriting your values
        $defaults = [
            'champions_enabled'             => '1',
            'champions_pending_hold_days'   => '14',
            'champions_pair_txn_cap'        => '2',
            'champions_block_repeat_winner' => '1',
            'champions_min_order_tzs'       => '0',
        ];
        foreach ($defaults as $name => $value) {
            if (!DB::table('static_options')->where('option_name', $name)->exists()) {
                DB::table('static_options')->insert([
                    'option_name' => $name, 'option_value' => $value,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        if (!DB::table('static_options')->where('option_name', 'champions_started_at')->exists()) {
            DB::table('static_options')->insert([
                'option_name' => 'champions_started_at', 'option_value' => now()->utc()->toDateTimeString(),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (['champion_disqualifications', 'champion_badges', 'champion_winners',
                  'champion_mission_progress', 'champion_missions', 'champion_points', 'champion_seasons'] as $table) {
            Schema::dropIfExists($table);
        }
        DB::table('static_options')->where('option_name', 'like', 'champions\_%')->delete();
    }
};
