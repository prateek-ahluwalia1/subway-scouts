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
        Schema::create('roster_complete_activity', function (Blueprint $table) {
            $table->id();
            $table->integer('roster_id');
            $table->string('activity');
            $table->string('type');
            $table->string('record_id');
            $table->string('activity_time');
            $table->string('activity_by');
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
        Schema::dropIfExists('roster_complete_activity');
    }
};
