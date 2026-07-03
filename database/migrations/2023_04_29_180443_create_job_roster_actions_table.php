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
        Schema::create('job_roster_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('action_by');
            $table->string('action_type');
            $table->string('action_on');
            $table->integer('roster_id');
            $table->longText('data')->nullable();
            $table->longText('updated_colums')->nullable();
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
        Schema::dropIfExists('job_roster_actions');
    }
};
