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
        Schema::create('job_breaks', function (Blueprint $table) {
            $table->id();
            $table->integer('roster_id');
            $table->integer('guard_id');
            $table->string('start_time');
            $table->string('end_time');
            $table->string('notes');
            $table->string('inform_to');
            $table->string('job_status')->default(0);
            $table->string('break_start_time')->nullable();
            $table->string('break_end_time')->nullable();
            $table->text('break_end_notes')->nullable();
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
        Schema::dropIfExists('job_breaks');
    }
};
