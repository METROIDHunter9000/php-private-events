<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->integer('organizer_id')->references('id')->on('user');
            $table->boolean('is_private');
            $table->date('date');
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });
        Schema::create('event_user_relations', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('user_id')->references('id')->on('user');
            $table->integer('event_id')->references('id')->on('event');
            $table->unique(['event_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_user_relations');
    }
};
