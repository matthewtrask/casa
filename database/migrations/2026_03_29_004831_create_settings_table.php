<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // Seed default keys so the settings page always has all rows to configure
        DB::table('settings')->insert([
            ['key' => 'slack_channel_default',     'label' => 'Default Slack Channel',     'value' => '#casa',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slack_channel_plant',       'label' => 'Plants Channel',            'value' => null,     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slack_channel_chore',       'label' => 'Chores Channel',            'value' => null,     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slack_channel_maintenance', 'label' => 'Maintenance Channel',       'value' => null,     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slack_channel_pet',         'label' => 'Pets Channel',              'value' => null,     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'slack_channel_other',       'label' => 'Other Channel',             'value' => null,     'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
