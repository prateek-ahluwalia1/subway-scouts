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
        Schema::create('welfare_call_data', function (Blueprint $table) {
            $table->id();
            $table->integer('job_roster_id');
            $table->integer('guard_id');
            $table->string('coordinates');
            $table->string('notes');
            $table->string('response_time');
            $table->enum('status', ['yes', 'no'])->nullable();
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
        Schema::dropIfExists('welfare_call_data');
    }
};
