<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trackable_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('species')->nullable();
            $table->string('location');
            $table->integer('action_frequency_days');
            $table->enum('category', ['plant', 'chore', 'maintenance', 'pet', 'other']);
            $table->enum('sunlight_needs', ['low', 'medium', 'high', 'direct'])->nullable();
            $table->timestamp('last_action_at')->nullable();
            $table->timestamp('last_secondary_action_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trackable_items');
    }
};
