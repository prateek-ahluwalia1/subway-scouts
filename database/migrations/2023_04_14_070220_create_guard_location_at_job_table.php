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
        Schema::create('guard_location_at_job', function (Blueprint $table) {
            $table->id();
            $table->integer('roster_id');
            $table->string('guard_id');
            $table->string('job_id');
            $table->string('coordinates');
            $table->string('event_time');
            $table->string('distance');
            $table->enum('seen_status', ['seen', 'unseen'])->default('unseen');
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
        Schema::dropIfExists('guard_location_at_job');
    }
};
