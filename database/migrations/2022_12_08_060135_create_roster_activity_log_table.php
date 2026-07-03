<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roster_activity_log', function (Blueprint $table) {
            $table->id();
            $table->integer('roster_id');
            $table->integer('action');
            $table->text('message');
            $table->string('action_by');
            $table->string('action_by_type');
            $table->string('action_by_person_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roster_activity_log');
    }
};
